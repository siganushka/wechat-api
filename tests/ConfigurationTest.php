<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wechat\Configuration;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class ConfigurationTest extends TestCase
{
    public function testAll(): void
    {
        $configuration = static::create();

        static::assertInstanceOf(\Countable::class, $configuration);
        static::assertInstanceOf(\IteratorAggregate::class, $configuration);
        static::assertInstanceOf(\ArrayAccess::class, $configuration);
        static::assertSame(2, $configuration->count());

        static::assertEquals([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
        ], $configuration->toArray());

        $configuration = static::create([
            'appid' => 'foo',
            'secret' => 'bar',
        ]);

        static::assertEquals([
            'appid' => 'foo',
            'secret' => 'bar',
        ], $configuration->toArray());
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        static::create([
            'appid' => 123,
            'secret' => 'test_secret',
        ]);
    }

    public function testSecretInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "secret" with value 123 is expected to be of type "string", but is of type "int"');

        static::create([
            'appid' => 'test_appid',
            'secret' => 123,
        ]);
    }

    public static function create(?array $configs = null): Configuration
    {
        if (null === $configs) {
            $configs = [
                'appid' => 'test_appid',
                'secret' => 'test_secret',
            ];
        }

        return new Configuration($configs);
    }
}
