<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationOptionsTest extends TestCase
{
    public function testDefinedOptions(): void
    {
        $resolver = new OptionsResolver();

        $options = static::create();
        $options->configure($resolver);

        static::assertSame([], $resolver->getDefinedOptions());

        $configuration = ConfigurationTest::create();
        $configuration->configure($resolver);

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

    public function testResolve(): void
    {
        $resolver = new OptionsResolver();

        $configuration = ConfigurationTest::create();
        $configuration->configure($resolver);

        $options = static::create();
        $options->configure($resolver);

        static::assertSame([
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => __DIR__.'/Mock/cert.pem',
            'mch_client_key' => __DIR__.'/Mock/key.pem',
            'sign_type' => 'HMAC-SHA256',
            'appid' => 'test_appid',
            'secret' => 'test_secret',
        ], $resolver->resolve());
    }

    public function testCustomOptions(): void
    {
        $resolver = new OptionsResolver();

        $configuration = ConfigurationTest::create();
        $configuration->configure($resolver);

        $options = static::create();
        $options->configure($resolver);

        static::assertSame([
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => __DIR__.'/Mock/cert.pem',
            'mch_client_key' => __DIR__.'/Mock/key.pem',
            'sign_type' => 'HMAC-SHA256',
            'appid' => 'foo',
            'secret' => 'bar',
        ], $resolver->resolve(['appid' => 'foo', 'secret' => 'bar']));
    }

    public static function create(Configuration $configuration = null): ConfigurationOptions
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::create();
        }

        return new ConfigurationOptions($configuration);
    }
}
