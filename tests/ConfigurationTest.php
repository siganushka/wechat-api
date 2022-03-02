<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class ConfigurationTest extends TestCase
{
    public function testAll(): void
    {
        $options = [
            'appid' => 'test_appid',
            'appsecret' => 'test_appsecret',
        ];

        $configuration = new Configuration($options);
        static::assertSame($options['appid'], $configuration['appid']);
        static::assertSame($options['appsecret'], $configuration['appsecret']);
        static::assertNull($configuration['mchid']);
        static::assertNull($configuration['mchkey']);
        static::assertNull($configuration['client_cert_file']);
        static::assertNull($configuration['client_key_file']);
        static::assertSame('MD5', $configuration['sign_type']);
    }

    public function testCustomOptions(): void
    {
        $options = [
            'appid' => 'test_appid',
            'appsecret' => 'test_appsecret',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'client_cert_file' => __DIR__.'/Mock/cert.pem',
            'client_key_file' => __DIR__.'/Mock/key.pem',
            'sign_type' => 'HMAC-SHA256',
        ];

        $configuration = new Configuration($options);
        static::assertSame($options['appid'], $configuration['appid']);
        static::assertSame($options['appsecret'], $configuration['appsecret']);
        static::assertSame($options['mchid'], $configuration['mchid']);
        static::assertSame($options['mchkey'], $configuration['mchkey']);
        static::assertSame($options['client_cert_file'], $configuration['client_cert_file']);
        static::assertSame($options['client_key_file'], $configuration['client_key_file']);
        static::assertSame($options['sign_type'], $configuration['sign_type']);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        new Configuration(['appsecret' => 'test_appsecret']);
    }

    public function testAppsecretMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appsecret" is missing');

        new Configuration(['appid' => 'test_appid']);
    }

    public function testCertFileInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "client_cert_file" file does not exists');

        new Configuration([
            'appid' => 'test_appid',
            'appsecret' => 'test_appsecret',
            'client_cert_file' => 'non_existing_file.pem',
        ]);
    }

    public function testKeyFileInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "client_key_file" file does not exists');

        new Configuration([
            'appid' => 'test_appid',
            'appsecret' => 'test_appsecret',
            'client_key_file' => 'non_existing_file.pem',
        ]);
    }

    public function testSignTypeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "sign_type" with value "invalid_sign_type" is invalid. Accepted values are: "MD5", "HMAC-SHA256"');

        new Configuration([
            'appid' => 'test_appid',
            'appsecret' => 'test_appsecret',
            'sign_type' => 'invalid_sign_type',
        ]);
    }
}
