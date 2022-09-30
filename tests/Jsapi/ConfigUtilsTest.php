<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\Jsapi;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wechat\Jsapi\ConfigUtils;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

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

    public function testResolve(): void
    {
        static::assertEquals([
            'timestamp' => 'test_timestamp',
            'noncestr' => 'test_noncestr',
            'url' => 'test_url',
            'apis' => [],
            'debug' => false,
            'appid' => 'test_appid',
            'ticket' => 'test_ticket',
        ], $this->configUtils->resolve([
            'appid' => 'test_appid',
            'ticket' => 'test_ticket',
            'timestamp' => 'test_timestamp',
            'noncestr' => 'test_noncestr',
            'url' => 'test_url',
        ]));

        static::assertEquals([
            'timestamp' => 'test_timestamp',
            'noncestr' => 'test_noncestr',
            'url' => 'test_url',
            'apis' => ['a', 'b', 'c'],
            'debug' => true,
            'appid' => 'test_appid',
            'ticket' => 'test_ticket',
        ], $this->configUtils->resolve([
            'appid' => 'test_appid',
            'ticket' => 'test_ticket',
            'timestamp' => 'test_timestamp',
            'noncestr' => 'test_noncestr',
            'url' => 'test_url',
            'apis' => ['a', 'b', 'c'],
            'debug' => true,
        ]));
    }

    public function testGenerate(): void
    {
        $configs = $this->configUtils->generate(['appid' => 'foo', 'ticket' => 'bar']);
        static::assertSame('foo', $configs['appId']);
        static::assertArrayHasKey('timestamp', $configs);
        static::assertArrayHasKey('nonceStr', $configs);
        static::assertArrayHasKey('signature', $configs);
        static::assertEquals([], $configs['jsApiList']);
        static::assertFalse($configs['debug']);

        $configs = $this->configUtils->generate([
            'appid' => 'foo',
            'ticket' => 'bar',
            'timestamp' => 'test_timestamp',
            'noncestr' => 'test_noncestr',
            'url' => '/foo',
            'apis' => ['a', 'b', 'c'],
            'debug' => true,
        ]);

        static::assertSame('foo', $configs['appId']);
        static::assertArrayHasKey('timestamp', $configs);
        static::assertArrayHasKey('nonceStr', $configs);
        static::assertArrayHasKey('signature', $configs);
        static::assertEquals(['a', 'b', 'c'], $configs['jsApiList']);
        static::assertTrue($configs['debug']);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        $this->configUtils->generate(['ticket' => 'bar']);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        $this->configUtils->generate(['appid' => 123, 'ticket' => 'bar']);
    }

    public function testTicketMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "ticket" is missing');

        $this->configUtils->generate(['appid' => 'foo']);
    }

    public function testTicketInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "ticket" with value 123 is expected to be of type "string", but is of type "int"');

        $this->configUtils->generate(['appid' => 'foo', 'ticket' => 123]);
    }

    public function testUrlInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "url" with value 123 is expected to be of type "string", but is of type "int"');

        $this->configUtils->generate(['appid' => 'foo', 'ticket' => 'bar', 'url' => 123]);
    }

    public function testApisInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "apis" with value 123 is expected to be of type "string[]", but is of type "int"');

        $this->configUtils->generate(['appid' => 'foo', 'ticket' => 'bar', 'apis' => 123]);
    }

    public function testDebugInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "debug" with value 123 is expected to be of type "bool", but is of type "int"');

        $this->configUtils->generate(['appid' => 'foo', 'ticket' => 'bar', 'debug' => 123]);
    }
}
