<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\ConfigurationManager;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Core\Token;
use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;
use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;
use Siganushka\ApiClient\Wechat\Payment\Query;
use Siganushka\ApiClient\Wechat\Payment\Refund;
use Siganushka\ApiClient\Wechat\Payment\Unifiedorder;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationOptionsTest extends TestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();

        $configurationOptions = static::create();
        $configurationOptions->configure($resolver);

        static::assertSame([
            'using_config',
            'appid',
            'secret',
            'mchid',
            'mchkey',
            'mch_client_cert',
            'mch_client_key',
        ], $resolver->getDefinedOptions());
    }

    public function testResolve(): void
    {
        $resolver = new OptionsResolver();

        $configurationOptions = static::create();
        $configurationOptions->configure($resolver);

        $resolved = $resolver->resolve();
        static::assertSame('default', $resolved['using_config']);
        static::assertSame('test_appid', $resolved['appid']);
        static::assertSame('test_secret', $resolved['secret']);
        static::assertSame('test_mchid', $resolved['mchid']);
        static::assertSame('test_mchkey', $resolved['mchkey']);
        static::assertSame(__DIR__.'/Mock/cert.pem', $resolved['mch_client_cert']);
        static::assertSame(__DIR__.'/Mock/key.pem', $resolved['mch_client_key']);

        $resolved = $resolver->resolve(['using_config' => 'custom']);
        static::assertSame('custom', $resolved['using_config']);
        static::assertSame('custom_appid', $resolved['appid']);
        static::assertSame('custom_secret', $resolved['secret']);
        static::assertNull($resolved['mchid']);
        static::assertNull($resolved['mchkey']);
        static::assertNull($resolved['mch_client_cert']);
        static::assertNull($resolved['mch_client_key']);

        $resolved = $resolver->resolve(['using_config' => 'custom', 'mchid' => 'foo', 'mchkey' => 'bar']);
        static::assertSame('custom', $resolved['using_config']);
        static::assertSame('custom_appid', $resolved['appid']);
        static::assertSame('custom_secret', $resolved['secret']);
        static::assertSame('foo', $resolved['mchid']);
        static::assertSame('bar', $resolved['mchkey']);
        static::assertNull($resolved['mch_client_cert']);
        static::assertNull($resolved['mch_client_key']);
    }

    public function testGetExtendedRequests(): void
    {
        $configurationOptions = static::create();

        $extendedRequests = $configurationOptions::getExtendedRequests();
        static::assertCount(7, $extendedRequests);
        static::assertContains(Token::class, $extendedRequests);
        static::assertContains(SessionKey::class, $extendedRequests);
        static::assertContains(RefreshToken::class, $extendedRequests);
        static::assertContains(Query::class, $extendedRequests);
        static::assertContains(Refund::class, $extendedRequests);
        static::assertContains(Unifiedorder::class, $extendedRequests);
    }

    public function testUsingConfigInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "using_config" with value "foo" is invalid. Accepted values are: "default", "custom"');

        $resolver = new OptionsResolver();

        $configurationOptions = static::create();
        $configurationOptions->configure($resolver);

        $resolver->resolve(['using_config' => 'foo']);
    }

    public static function create(ConfigurationManager $configurationManager = null): ConfigurationOptions
    {
        if (null === $configurationManager) {
            $configurationManager = ConfigurationManagerTest::create();
        }

        return new ConfigurationOptions($configurationManager);
    }
}
