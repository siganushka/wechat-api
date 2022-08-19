<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Core\CallbackIp;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Siganushka\ApiClient\Wechat\Core\TokenOptions;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Siganushka\ApiClient\Wechat\Miniapp\WxacodeUnlimited;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
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

    public function testGetExtendedRequests(): void
    {
        $tokenOptions = static::create();

        $extendedRequests = $tokenOptions::getExtendedRequests();
        static::assertCount(7, $extendedRequests);
        static::assertContains(CallbackIp::class, $extendedRequests);
        static::assertContains(ServerIp::class, $extendedRequests);
        static::assertContains(Qrcode::class, $extendedRequests);
        static::assertContains(Wxacode::class, $extendedRequests);
        static::assertContains(WxacodeUnlimited::class, $extendedRequests);
        static::assertContains(Message::class, $extendedRequests);
        static::assertContains(Ticket::class, $extendedRequests);
    }

    public static function create(Configuration $configuration = null): TokenOptions
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::create();
        }

        $client = new MockHttpClient([
            ResponseFactory::createMockResponseWithJson([
                'access_token' => 'test_token_1',
                'expires_in' => 1024,
            ]),
            ResponseFactory::createMockResponseWithJson([
                'access_token' => 'test_token_2',
                'expires_in' => 1024,
            ]),
        ]);

        $cache = new NullAdapter();

        return new TokenOptions($configuration, $client, $cache);
    }
}
