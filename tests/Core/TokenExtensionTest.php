<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wechat\Core\CallbackIp;
use Siganushka\ApiFactory\Wechat\Core\ServerIp;
use Siganushka\ApiFactory\Wechat\Core\Ticket;
use Siganushka\ApiFactory\Wechat\Core\TokenExtension;
use Siganushka\ApiFactory\Wechat\Miniapp\Qrcode;
use Siganushka\ApiFactory\Wechat\Miniapp\Wxacode;
use Siganushka\ApiFactory\Wechat\Miniapp\WxacodeUnlimited;
use Siganushka\ApiFactory\Wechat\Template\Message;
use Siganushka\ApiFactory\Wechat\Tests\ConfigurationTest;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TokenExtensionTest extends TestCase
{
    protected TokenExtension $extension;

    protected function setUp(): void
    {
        $configuration = ConfigurationTest::create();

        $client = new MockHttpClient([
            new MockResponse(json_encode(['access_token' => 'test_token_1', 'expires_in' => 1024], \JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode(['access_token' => 'test_token_2', 'expires_in' => 1024], \JSON_THROW_ON_ERROR)),
        ]);

        $cache = new NullAdapter();

        $this->extension = new TokenExtension($configuration, $client, $cache);
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->extension->configureOptions($resolver);

        static::assertEquals([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'token' => 'test_token_1',
        ], $resolver->resolve());

        static::assertEquals([
            'appid' => 'foo',
            'secret' => 'bar',
            'token' => 'test_token_2',
        ], $resolver->resolve(['appid' => 'foo', 'secret' => 'bar']));
    }

    public function testGetExtendedClasses(): void
    {
        static::assertEquals([
            CallbackIp::class,
            ServerIp::class,
            Qrcode::class,
            Wxacode::class,
            WxacodeUnlimited::class,
            Message::class,
            Ticket::class,
        ], TokenExtension::getExtendedClasses());
    }
}
