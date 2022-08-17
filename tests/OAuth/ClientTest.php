<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\OAuth\Client;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientTest extends TestCase
{
    private ?Client $client = null;

    protected function setUp(): void
    {
        $this->client = new Client();
    }

    protected function tearDown(): void
    {
        $this->client = null;
    }

    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->client->configure($resolver);

        static::assertSame([
            'appid',
            'redirect_uri',
            'state',
            'scope',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'state' => null,
            'scope' => 'snsapi_base',
            'appid' => 'foo',
            'redirect_uri' => '/bar',
        ], $resolver->resolve(['appid' => 'foo', 'redirect_uri' => '/bar']));

        static::assertSame([
            'state' => 'baz',
            'scope' => 'snsapi_userinfo',
            'appid' => 'foo',
            'redirect_uri' => '/bar',
        ], $resolver->resolve(['appid' => 'foo', 'redirect_uri' => '/bar', 'state' => 'baz', 'scope' => 'snsapi_userinfo']));
    }

    public function testGetRedirectUrl(): void
    {
        $redirectUrl = $this->client->getRedirectUrl(['appid' => 'foo', 'redirect_uri' => '/bar']);
        static::assertStringStartsWith(Client::URL, $redirectUrl);
        static::assertStringEndsWith('#wechat_redirect', $redirectUrl);
        static::assertStringContainsString('appid=foo', $redirectUrl);
        static::assertStringContainsString(urlencode('/bar'), $redirectUrl);
        static::assertStringContainsString('scope=snsapi_base', $redirectUrl);
        static::assertStringNotContainsString('state=', $redirectUrl);

        $redirectUrl = $this->client->getRedirectUrl(['appid' => 'foo', 'redirect_uri' => '/bar', 'state' => 'baz', 'scope' => 'snsapi_userinfo']);
        static::assertStringContainsString('scope=snsapi_userinfo', $redirectUrl);
        static::assertStringContainsString('state=baz', $redirectUrl);
    }
}
