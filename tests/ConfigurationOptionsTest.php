<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Core\Token;
use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken;
use Siganushka\ApiClient\Wechat\OAuth\Client;
use Siganushka\ApiClient\Wechat\OAuth\Qrcode;
use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;
use Siganushka\ApiClient\Wechat\Payment\ParameterUtils;
use Siganushka\ApiClient\Wechat\Payment\Query;
use Siganushka\ApiClient\Wechat\Payment\Refund;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;
use Siganushka\ApiClient\Wechat\Payment\Transfer;
use Siganushka\ApiClient\Wechat\Payment\Unifiedorder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationOptionsTest extends TestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();

        $configurationOptions = static::create();
        $configurationOptions->configure($resolver);

        static::assertSame([
            'appid',
            'secret',
            'mchid',
            'mchkey',
            'mch_client_cert',
            'mch_client_key',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
        ], $resolver->resolve());

        static::assertSame([
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'appid' => 'foo',
            'secret' => 'bar',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
        ], $resolver->resolve([
            'appid' => 'foo',
            'secret' => 'bar',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
        ]));
    }

    public function testGetExtendedClasses(): void
    {
        $configurationOptions = static::create();

        static::assertSame([
            Token::class,
            SessionKey::class,
            Client::class,
            Qrcode::class,
            AccessToken::class,
            RefreshToken::class,
            Query::class,
            Refund::class,
            Transfer::class,
            ParameterUtils::class,
            SignatureUtils::class,
            Unifiedorder::class,
        ], $configurationOptions::getExtendedClasses());
    }

    public static function create(Configuration $configuration = null): ConfigurationOptions
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::create();
        }

        return new ConfigurationOptions($configuration);
    }
}
