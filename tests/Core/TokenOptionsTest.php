<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Core\CallbackIp;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Siganushka\ApiClient\Wechat\Core\Ticket;
use Siganushka\ApiClient\Wechat\Core\TokenOptions;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Siganushka\ApiClient\Wechat\Miniapp\WxacodeUnlimited;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TokenOptionsTest extends TestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();

        $tokenOptions = static::create();
        $tokenOptions->configure($resolver);

        static::assertSame([
            'appid',
            'secret',
            'mchid',
            'mchkey',
            'mch_client_cert',
            'mch_client_key',
            'token',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
            'token' => 'test_token_1',
        ], $resolver->resolve());

        static::assertSame([
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
            'appid' => 'foo',
            'secret' => 'bar',
            'token' => 'test_token_2',
        ], $resolver->resolve(['appid' => 'foo', 'secret' => 'bar']));
    }

    public function testGetExtendedClasses(): void
    {
        $tokenOptions = static::create();

        $extendedClasses = $tokenOptions::getExtendedClasses();
        static::assertCount(7, $extendedClasses);
        static::assertContains(CallbackIp::class, $extendedClasses);
        static::assertContains(ServerIp::class, $extendedClasses);
        static::assertContains(Qrcode::class, $extendedClasses);
        static::assertContains(Wxacode::class, $extendedClasses);
        static::assertContains(WxacodeUnlimited::class, $extendedClasses);
        static::assertContains(Message::class, $extendedClasses);
        static::assertContains(Ticket::class, $extendedClasses);
    }

    public static function create(Configuration $configuration = null): TokenOptions
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::create();
        }

        $client = new MockHttpClient([
            new MockResponse(json_encode(['access_token' => 'test_token_1', 'expires_in' => 1024])),
            new MockResponse(json_encode(['access_token' => 'test_token_2', 'expires_in' => 1024])),
        ]);

        $cache = new NullAdapter();

        return new TokenOptions($configuration, $client, $cache);
    }
}
