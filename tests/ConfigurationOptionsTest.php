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
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
        ], $resolver->resolve());

        static::assertSame([
            'appid' => 'foo',
            'secret' => 'bar',
        ], $resolver->resolve([
            'appid' => 'foo',
            'secret' => 'bar',
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
