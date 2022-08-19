<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationTest extends TestCase
{
    public const MCH_CLIENT_CERT = __DIR__.'/Mock/cert.pem';
    public const MCH_CLIENT_KEY = __DIR__.'/Mock/key.pem';

    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();

        $configuration = static::create();
        $configuration->configure($resolver);

        static::assertSame([
            'appid',
            'secret',
            'mchid',
            'mchkey',
            'mch_client_cert',
            'mch_client_key',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'appid' => null,
            'secret' => null,
            'mchid' => null,
            'mchkey' => null,
            'mch_client_cert' => null,
            'mch_client_key' => null,
        ], $resolver->resolve());

        static::assertSame([
            'appid' => 'foo',
            'secret' => 'bar',
            'mchid' => null,
            'mchkey' => null,
            'mch_client_cert' => static::MCH_CLIENT_CERT,
            'mch_client_key' => static::MCH_CLIENT_KEY,
        ], $resolver->resolve([
            'appid' => 'foo',
            'secret' => 'bar',
            'mchid' => null,
            'mchkey' => null,
            'mch_client_cert' => static::MCH_CLIENT_CERT,
            'mch_client_key' => static::MCH_CLIENT_KEY,
        ]));
    }

    public function testAll(): void
    {
        $configuration = static::create();

        static::assertInstanceOf(\Countable::class, $configuration);
        static::assertInstanceOf(\IteratorAggregate::class, $configuration);
        static::assertInstanceOf(\ArrayAccess::class, $configuration);
        static::assertSame(6, $configuration->count());

        static::assertSame([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => static::MCH_CLIENT_CERT,
            'mch_client_key' => static::MCH_CLIENT_KEY,
        ], $configuration->toArray());

        $configuration = static::create([
            'appid' => 'foo',
            'secret' => 'bar',
            'mch_client_cert' => static::MCH_CLIENT_CERT,
            'mch_client_key' => static::MCH_CLIENT_KEY,
        ]);

        static::assertSame([
            'appid' => 'foo',
            'secret' => 'bar',
            'mchid' => null,
            'mchkey' => null,
            'mch_client_cert' => static::MCH_CLIENT_CERT,
            'mch_client_key' => static::MCH_CLIENT_KEY,
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

    public function testMchidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mchid" with value 123 is expected to be of type "string" or "null", but is of type "int"');

        static::create(['mchid' => 123]);
    }

    public function testMchkeyInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mchkey" with value 123 is expected to be of type "string" or "null", but is of type "int"');

        static::create(['mchkey' => 123]);
    }

    public function testMchClientCertInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mch_client_cert" with value 123 is expected to be of type "string" or "null", but is of type "int"');

        static::create(['mch_client_cert' => 123]);
    }

    public function testMchClientCertFileNotFoundException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mch_client_cert" file does not exists');

        static::create(['mch_client_cert' => 'non_existing_file.pem']);
    }

    public function testMchClientKeyInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mch_client_key" with value 123 is expected to be of type "string" or "null", but is of type "int"');

        static::create(['mch_client_key' => 123]);
    }

    public function testMchClientKeyFileNotFoundException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mch_client_key" file does not exists');

        static::create(['mch_client_key' => 'non_existing_file.pem']);
    }

    public static function create(array $configs = null): Configuration
    {
        if (null === $configs) {
            $configs = [
                'appid' => 'test_appid',
                'secret' => 'test_secret',
                'mchid' => 'test_mchid',
                'mchkey' => 'test_mchkey',
                'mch_client_cert' => static::MCH_CLIENT_CERT,
                'mch_client_key' => static::MCH_CLIENT_KEY,
            ];
        }

        return new Configuration($configs);
    }
}
