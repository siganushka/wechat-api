<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class ConfigurationTest extends TestCase
{
    public function testAll(): void
    {
        $options = [
            'appid' => 'test_appid',
            'secret' => 'test_secret',
        ];

        $configuration = new Configuration($options);
        static::assertSame($options['appid'], $configuration['appid']);
        static::assertSame($options['secret'], $configuration['secret']);
        static::assertNull($configuration['open_appid']);
        static::assertNull($configuration['open_secret']);
        static::assertNull($configuration['mchid']);
        static::assertNull($configuration['mchkey']);
        static::assertNull($configuration['client_cert_file']);
        static::assertNull($configuration['client_key_file']);
        static::assertSame('MD5', $configuration['sign_type']);
    }

    public function testCustomOptions(): void
    {
        $configuration = static::createConfiguration();
        static::assertSame('test_appid', $configuration['appid']);
        static::assertSame('test_secret', $configuration['secret']);
        static::assertSame('test_open_appid', $configuration['open_appid']);
        static::assertSame('test_open_secret', $configuration['open_secret']);
        static::assertSame('test_mchid', $configuration['mchid']);
        static::assertSame('test_mchkey', $configuration['mchkey']);
        static::assertSame(__DIR__.'/Mock/cert.pem', $configuration['client_cert_file']);
        static::assertSame(__DIR__.'/Mock/key.pem', $configuration['client_key_file']);
        static::assertSame('HMAC-SHA256', $configuration['sign_type']);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        new Configuration(['secret' => 'test_secret']);
    }

    public function testSecretMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "secret" is missing');

        new Configuration(['appid' => 'test_appid']);
    }

    public function testCertFileInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "client_cert_file" file does not exists');

        new Configuration([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'client_cert_file' => 'non_existing_file.pem',
        ]);
    }

    public function testKeyFileInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "client_key_file" file does not exists');

        new Configuration([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'client_key_file' => 'non_existing_file.pem',
        ]);
    }

    public function testSignTypeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "sign_type" with value "invalid_sign_type" is invalid. Accepted values are: "MD5", "HMAC-SHA256"');

        new Configuration([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'sign_type' => 'invalid_sign_type',
        ]);
    }

    public static function createConfiguration(): Configuration
    {
        $options = [
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'open_appid' => 'test_open_appid',
            'open_secret' => 'test_open_secret',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'client_cert_file' => __DIR__.'/Mock/cert.pem',
            'client_key_file' => __DIR__.'/Mock/key.pem',
            'sign_type' => 'HMAC-SHA256',
        ];

        return new Configuration($options);
    }

    public static function createXmlEncoder(): XmlEncoder
    {
        $context = [
            XmlEncoder::ENCODING => 'UTF-8',
            XmlEncoder::FORMAT_OUTPUT => true,
        ];

        return new XmlEncoder($context);
    }
}
