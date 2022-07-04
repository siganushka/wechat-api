<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\OAuth\Client;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;

class ClientTest extends TestCase
{
    public function testAll(): void
    {
        $options = [
            'redirect_uri' => 'http://localhost',
        ];

        $client = static::createClient();

        $resolved = $client->resolve($options);
        static::assertNull($resolved['state']);
        static::assertFalse($resolved['using_open_api']);
        static::assertSame('snsapi_base', $resolved['scope']);
        static::assertSame('http://localhost', $resolved['redirect_uri']);

        $redirectUrl = $client->getRedirectUrl($options);
        static::assertStringStartsWith(Client::URL, $redirectUrl);
        static::assertStringEndsWith('#wechat_redirect', $redirectUrl);
        static::assertStringContainsString('test_appid', $redirectUrl);
        static::assertStringContainsString('snsapi_base', $redirectUrl);
        static::assertStringContainsString(urlencode($options['redirect_uri']), $redirectUrl);

        $redirectUrl = $client->getRedirectUrl($options + ['state' => 'foo', 'using_open_api' => true]);
        static::assertStringStartsWith(Client::URL2, $redirectUrl);
        static::assertStringEndsWith('#wechat_redirect', $redirectUrl);
        static::assertStringContainsString('test_open_appid', $redirectUrl);
        static::assertStringContainsString('snsapi_login', $redirectUrl);
        static::assertStringContainsString('state', $redirectUrl);
        static::assertStringContainsString(urlencode($options['redirect_uri']), $redirectUrl);
    }

    public function testOpenAppidNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "open_appid" option');

        $configuration = new Configuration([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
        ]);

        $client = static::createClient($configuration);
        $client->getRedirectUrl([
            'redirect_uri' => 'http://localhost',
            'using_open_api' => true,
        ]);
    }

    public function testUsingOpenApiOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "using_open_api" with value 1 is expected to be of type "bool", but is of type "int"');

        $client = static::createClient();
        $client->getRedirectUrl([
            'redirect_uri' => 'http://localhost',
            'using_open_api' => 1,
        ]);
    }

    public static function createClient(Configuration $configuration = null): Client
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::createConfiguration();
        }

        return new Client($configuration);
    }
}
