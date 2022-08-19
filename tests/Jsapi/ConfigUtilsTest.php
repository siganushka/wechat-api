<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Jsapi;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
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

    public function testConfigure(): void
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

        static::assertSame([
            'timestamp' => 'test_timestamp',
            'nonce_str' => 'test_nonce_str',
            'url' => 'test_url',
            'apis' => [],
            'debug' => false,
            'appid' => 'test_appid',
            'ticket' => 'test_ticket',
        ], $resolver->resolve([
            'appid' => 'test_appid',
            'ticket' => 'test_ticket',
            'timestamp' => 'test_timestamp',
            'nonce_str' => 'test_nonce_str',
            'url' => 'test_url',
        ]));

        static::assertSame([
            'timestamp' => 'test_timestamp',
            'nonce_str' => 'test_nonce_str',
            'url' => 'test_url',
            'apis' => ['a', 'b', 'c'],
            'debug' => true,
            'appid' => 'test_appid',
            'ticket' => 'test_ticket',
        ], $resolver->resolve([
            'appid' => 'test_appid',
            'ticket' => 'test_ticket',
            'timestamp' => 'test_timestamp',
            'nonce_str' => 'test_nonce_str',
            'url' => 'test_url',
            'apis' => ['a', 'b', 'c'],
            'debug' => true,
        ]));
    }

    public function testGenerate(): void
    {
        $configs = $this->configUtils->generateFromOptions(['appid' => 'foo', 'ticket' => 'bar']);
        static::assertSame('foo', $configs['appId']);
        static::assertArrayHasKey('timestamp', $configs);
        static::assertArrayHasKey('nonceStr', $configs);
        static::assertArrayHasKey('signature', $configs);
        static::assertSame([], $configs['jsApiList']);
        static::assertFalse($configs['debug']);

        $configs = $this->configUtils->generateFromOptions([
            'appid' => 'foo',
            'ticket' => 'bar',
            'timestamp' => 'test_timestamp',
            'nonce_str' => 'test_nonce_str',
            'url' => '/foo',
            'apis' => ['a', 'b', 'c'],
            'debug' => true,
        ]);

        static::assertSame('foo', $configs['appId']);
        static::assertArrayHasKey('timestamp', $configs);
        static::assertArrayHasKey('nonceStr', $configs);
        static::assertArrayHasKey('signature', $configs);
        static::assertSame(['a', 'b', 'c'], $configs['jsApiList']);
        static::assertTrue($configs['debug']);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        $this->configUtils->generateFromOptions(['ticket' => 'bar']);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        $this->configUtils->generateFromOptions(['appid' => 123, 'ticket' => 'bar']);
    }

    public function testTicketMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "ticket" is missing');

        $this->configUtils->generateFromOptions(['appid' => 'foo']);
    }

    public function testTicketInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "ticket" with value 123 is expected to be of type "string", but is of type "int"');

        $this->configUtils->generateFromOptions(['appid' => 'foo', 'ticket' => 123]);
    }

    public function testUrlInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "url" with value 123 is expected to be of type "string", but is of type "int"');

        $this->configUtils->generateFromOptions(['appid' => 'foo', 'ticket' => 'bar', 'url' => 123]);
    }

    public function testApisInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "apis" with value 123 is expected to be of type "string[]", but is of type "int"');

        $this->configUtils->generateFromOptions(['appid' => 'foo', 'ticket' => 'bar', 'apis' => 123]);
    }

    public function testDebugInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "debug" with value 123 is expected to be of type "bool", but is of type "int"');

        $this->configUtils->generateFromOptions(['appid' => 'foo', 'ticket' => 'bar', 'debug' => 123]);
    }
}
