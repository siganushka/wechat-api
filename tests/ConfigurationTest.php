<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
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
            'mchid',
            'mchkey',
            'mch_client_cert',
            'mch_client_key',
        ], $resolver->getDefinedOptions());
    }

    public function testAll(): void
    {
        $configuration = static::create();

        static::assertSame('test_appid', $configuration['appid']);
        static::assertSame('test_secret', $configuration['secret']);
        static::assertSame('test_mchid', $configuration['mchid']);
        static::assertSame('test_mchkey', $configuration['mchkey']);
        static::assertSame(__DIR__.'/Mock/cert.pem', $configuration['mch_client_cert']);
        static::assertSame(__DIR__.'/Mock/key.pem', $configuration['mch_client_key']);

        $configuration = static::create([
            'appid' => 'foo',
            'secret' => 'bar',
        ]);

        static::assertSame('foo', $configuration['appid']);
        static::assertSame('bar', $configuration['secret']);
        static::assertNull($configuration['mchid']);
        static::assertNull($configuration['mchkey']);
        static::assertNull($configuration['mch_client_cert']);
        static::assertNull($configuration['mch_client_key']);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        static::create(['secret' => 'test_secret']);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        static::create(['appid' => 123, 'secret' => 'test_secret']);
    }

    public function testSecretMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "secret" is missing');

        static::create(['appid' => 'test_appid']);
    }

    public function testSecretInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "secret" with value 123 is expected to be of type "string", but is of type "int"');

        static::create(['appid' => 'test_appid', 'secret' => 123]);
    }

    public function testMchidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mchid" with value 123 is expected to be of type "null" or "string", but is of type "int"');

        static::create([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mchid' => 123,
        ]);
    }

    public function testMchkeyInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mchkey" with value 123 is expected to be of type "null" or "string", but is of type "int"');

        static::create([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mchkey' => 123,
        ]);
    }

    public function testClientCertInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mch_client_cert" with value 123 is expected to be of type "null" or "string", but is of type "int"');

        static::create([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mch_client_cert' => 123,
        ]);
    }

    public function testClientCertFileNotFoundException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mch_client_cert" file does not exists');

        static::create([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mch_client_cert' => 'non_existing_file.pem',
        ]);
    }

    public function testClientKeyInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mch_client_key" with value 123 is expected to be of type "null" or "string", but is of type "int"');

        static::create([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mch_client_key' => 123,
        ]);
    }

    public function testClientKeyFileNotFoundException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mch_client_key" file does not exists');

        static::create([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mch_client_key' => 'non_existing_file.pem',
        ]);
    }

    public static function create(array $configs = null): Configuration
    {
        if (null === $configs) {
            $configs = [
                'appid' => 'test_appid',
                'secret' => 'test_secret',
                'mchid' => 'test_mchid',
                'mchkey' => 'test_mchkey',
                'mch_client_cert' => __DIR__.'/Mock/cert.pem',
                'mch_client_key' => __DIR__.'/Mock/key.pem',
            ];
        }

        return new Configuration($configs);
    }
}
