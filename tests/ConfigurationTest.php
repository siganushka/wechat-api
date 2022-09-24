<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationTest extends TestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();

        $configuration = static::create();
        $configuration->configure($resolver);

        static::assertSame([
            'appid',
            'secret',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'appid' => null,
            'secret' => null,
        ], $resolver->resolve());

        static::assertSame([
            'appid' => 'foo',
            'secret' => 'bar',
        ], $resolver->resolve(['appid' => 'foo', 'secret' => 'bar']));
    }

    public function testAll(): void
    {
        $configuration = static::create();

        static::assertInstanceOf(\Countable::class, $configuration);
        static::assertInstanceOf(\IteratorAggregate::class, $configuration);
        static::assertInstanceOf(\ArrayAccess::class, $configuration);
        static::assertSame(2, $configuration->count());

        static::assertSame([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
        ], $configuration->toArray());

        $configuration = static::create([
            'appid' => 'foo',
            'secret' => 'bar',
        ]);

        static::assertSame([
            'appid' => 'foo',
            'secret' => 'bar',
        ], $configuration->toArray());
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string" or "null", but is of type "int"');

        static::create(['appid' => 123]);
    }

    public function testSecretInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "secret" with value 123 is expected to be of type "string" or "null", but is of type "int"');

        static::create(['secret' => 123]);
    }

    public static function create(array $configs = null): Configuration
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
