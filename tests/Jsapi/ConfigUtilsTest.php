<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Jsapi;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationOptionsTest;
use Siganushka\ApiClient\Wechat\Tests\Ticket\TicketOptionsTest;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigUtilsTest extends TestCase
{
    private ?ConfigUtils $configUtils = null;

    protected function setUp(): void
    {
        $this->configUtils = new ConfigUtils();
    }

    protected function tearDown(): void
    {
        $this->configUtils = null;
    }

    public function testDefinedOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->configUtils->configure($resolver);

        static::assertSame([
            'appid',
            'ticket',
            'timestamp',
            'nonce_str',
            'url',
            'apis',
            'debug',
        ], $resolver->getDefinedOptions());
    }

    public function testResolve(): void
    {
        $resolver = new OptionsResolver();
        $this->configUtils->configure($resolver);

        $resolved = $resolver->resolve(['appid' => 'foo', 'ticket' => 'bar']);
        static::assertSame('foo', $resolved['appid']);
        static::assertSame('bar', $resolved['ticket']);
        static::assertArrayHasKey('url', $resolved);
        static::assertArrayHasKey('apis', $resolved);
        static::assertArrayHasKey('debug', $resolved);

        $options = ConfigurationOptionsTest::create();
        $options->configure($resolver);

        $options = TicketOptionsTest::create();
        $options->configure($resolver);

        $resolved = $resolver->resolve([
            'url' => '/foo',
            'apis' => ['a', 'b', 'c'],
            'debug' => true,
        ]);

        static::assertSame('test_appid', $resolved['appid']);
        static::assertSame('test_ticket', $resolved['ticket']);
        static::assertSame('/foo', $resolved['url']);
        static::assertSame(['a', 'b', 'c'], $resolved['apis']);
        static::assertTrue($resolved['debug']);
    }

    public function testGenerate(): void
    {
        $this->configUtils->using(ConfigurationOptionsTest::create());
        $this->configUtils->using(TicketOptionsTest::create());

        $configs = $this->configUtils->generate(['a', 'b', 'c'], true);

        static::assertArrayHasKey('timestamp', $configs);
        static::assertArrayHasKey('nonceStr', $configs);
        static::assertArrayHasKey('signature', $configs);

        static::assertSame('test_appid', $configs['appId']);
        static::assertSame(['a', 'b', 'c'], $configs['jsApiList']);
        static::assertTrue($configs['debug']);
    }

    public function testGenerateFromOptions(): void
    {
        $configs = $this->configUtils->generateFromOptions([
            'appid' => 'foo',
            'ticket' => 'bar',
            'url' => '/foo',
            'apis' => ['test_api'],
            'debug' => true,
        ]);

        static::assertArrayHasKey('timestamp', $configs);
        static::assertArrayHasKey('nonceStr', $configs);
        static::assertArrayHasKey('signature', $configs);

        static::assertSame('foo', $configs['appId']);
        static::assertSame(['test_api'], $configs['jsApiList']);
        static::assertTrue($configs['debug']);
    }
}
