<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wechat\Core\TicketExtension;
use Siganushka\ApiFactory\Wechat\Jsapi\ConfigUtils;
use Siganushka\ApiFactory\Wechat\Tests\ConfigurationTest;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketExtensionTest extends TestCase
{
    protected TicketExtension $extension;

    protected function setUp(): void
    {
        $configuration = ConfigurationTest::create();

        $client = new MockHttpClient([
            new MockResponse(json_encode(['access_token' => 'test_token', 'expires_in' => 1024], \JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode(['ticket' => 'test_ticket'], \JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode(['access_token' => 'test_token_for_wx_card', 'expires_in' => 1024], \JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode(['ticket' => 'test_ticket_for_wx_card'], \JSON_THROW_ON_ERROR)),
        ]);

        $cache = new NullAdapter();

        $this->extension = new TicketExtension($configuration, $client, $cache);
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->extension->configureOptions($resolver);

        static::assertEquals([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'type' => 'jsapi',
            'token' => 'test_token',
            'ticket' => 'test_ticket',
        ], $resolver->resolve());

        static::assertEquals([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'type' => 'wx_card',
            'token' => 'test_token_for_wx_card',
            'ticket' => 'test_ticket_for_wx_card',
        ], $resolver->resolve(['type' => 'wx_card']));
    }

    public function testGetExtendedClasses(): void
    {
        static::assertEquals([
            ConfigUtils::class,
        ], TicketExtension::getExtendedClasses());
    }
}
