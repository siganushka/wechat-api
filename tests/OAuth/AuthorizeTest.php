<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\OAuth\Authorize;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class AuthorizeTest extends TestCase
{
    public function testAll(): void
    {
        $options = [
            'redirect_uri' => 'http://localhost',
            'state' => 'foo',
        ];

        $authorize = static::createAuthorize();

        $authorizeUrl = $authorize->getAuthorizeUrl($options);
        static::assertStringStartsWith(Authorize::URL, $authorizeUrl);
        static::assertStringEndsWith('#wechat_redirect', $authorizeUrl);
        static::assertStringContainsString('test_appid', $authorizeUrl);
        static::assertStringContainsString('snsapi_base', $authorizeUrl);
        static::assertStringContainsString('state', $authorizeUrl);
        static::assertStringContainsString(urlencode($options['redirect_uri']), $authorizeUrl);

        $authorizeUrl = $authorize->getAuthorizeUrl($options + ['using_open_api' => true]);
        static::assertStringStartsWith(Authorize::URL2, $authorizeUrl);
        static::assertStringEndsWith('#wechat_redirect', $authorizeUrl);
        static::assertStringContainsString('test_open_appid', $authorizeUrl);
        static::assertStringContainsString('snsapi_login', $authorizeUrl);
        static::assertStringContainsString('state', $authorizeUrl);
        static::assertStringContainsString(urlencode($options['redirect_uri']), $authorizeUrl);
    }

    public function testUsingOpenApiOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "using_open_api" with value 1 is expected to be of type "bool", but is of type "int"');

        $authorize = static::createAuthorize();
        $authorize->getAuthorizeUrl([
            'redirect_uri' => 'http://localhost',
            'using_open_api' => 1,
        ]);
    }

    public static function createAuthorize(): Authorize
    {
        $configuration = ConfigurationTest::createConfiguration();
        $authorize = new Authorize($configuration);

        return $authorize;
    }
}
