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
    public function testDefinedOptions(): void
    {
        $resolver = new OptionsResolver();

        $extension = static::create();
        $extension->configure($resolver);

        static::assertSame([
            'appid',
            'secret',
            'mchid',
            'mchkey',
            'mch_client_cert',
            'mch_client_key',
            'sign_type',
        ], $resolver->getDefinedOptions());
    }

    public function testAll(): void
    {
        $configuration = static::create([
            'appid' => 'foo',
            'secret' => 'bar',
        ]);

        static::assertTrue($configuration->offsetExists('appid'));
        static::assertTrue($configuration->offsetExists('secret'));
        static::assertTrue($configuration->offsetExists('mchid'));
        static::assertTrue($configuration->offsetExists('mchkey'));
        static::assertTrue($configuration->offsetExists('mch_client_cert'));
        static::assertTrue($configuration->offsetExists('mch_client_key'));
        static::assertTrue($configuration->offsetExists('sign_type'));

        static::assertSame([
            'mchid' => null,
            'mchkey' => null,
            'mch_client_cert' => null,
            'mch_client_key' => null,
            'sign_type' => 'MD5',
            'appid' => 'foo',
            'secret' => 'bar',
        ], $configuration->toArray());
    }

    public function testCustomOptions(): void
    {
        $configuration = static::create();
        static::assertSame([
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => __DIR__.'/Mock/cert.pem',
            'mch_client_key' => __DIR__.'/Mock/key.pem',
            'sign_type' => 'HMAC-SHA256',
            'appid' => 'test_appid',
            'secret' => 'test_secret',
        ], $configuration->toArray());
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
        $this->expectExceptionMessage('The option "mch_client_key" file does not exists');

        static::create([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mch_client_key' => 'non_existing_file.pem',
        ]);
    }

    public function testSignTypeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "sign_type" with value "invalid_sign_type" is invalid. Accepted values are: "MD5", "HMAC-SHA256"');

        static::create([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'sign_type' => 'invalid_sign_type',
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
                'sign_type' => 'HMAC-SHA256',
            ];
        }

        return new Configuration($configs);
    }
}
