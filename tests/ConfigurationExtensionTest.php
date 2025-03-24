<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wechat\ConfigurationExtension;
use Siganushka\ApiFactory\Wechat\Core\Token;
use Siganushka\ApiFactory\Wechat\Core\TokenStable;
use Siganushka\ApiFactory\Wechat\Miniapp\SessionKey;
use Siganushka\ApiFactory\Wechat\OAuth\AccessToken;
use Siganushka\ApiFactory\Wechat\OAuth\Client;
use Siganushka\ApiFactory\Wechat\OAuth\Qrcode;
use Siganushka\ApiFactory\Wechat\OAuth\RefreshToken;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationExtensionTest extends TestCase
{
    protected ConfigurationExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new ConfigurationExtension(ConfigurationTest::create());
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['appid', 'secret']);
        $this->extension->configureOptions($resolver);

        static::assertEquals([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
        ], $resolver->resolve());

        static::assertEquals([
            'appid' => 'foo',
            'secret' => 'bar',
        ], $resolver->resolve([
            'appid' => 'foo',
            'secret' => 'bar',
        ]));
    }

    public function testGetExtendedClasses(): void
    {
        static::assertEquals([
            Token::class,
            TokenStable::class,
            SessionKey::class,
            Client::class,
            Qrcode::class,
            AccessToken::class,
            RefreshToken::class,
        ], ConfigurationExtension::getExtendedClasses());
    }
}
