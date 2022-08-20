<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\OAuth\Client;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
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

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        $this->client->getRedirectUrl(['redirect_uri' => '/bar']);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        $this->client->getRedirectUrl(['appid' => 123, 'redirect_uri' => '/bar']);
    }

    public function testRedirectUriMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "redirect_uri" is missing');

        $this->client->getRedirectUrl(['appid' => 'foo']);
    }

    public function testRedirectUriInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "redirect_uri" with value 123 is expected to be of type "string", but is of type "int"');

        $this->client->getRedirectUrl(['appid' => 'foo', 'redirect_uri' => 123]);
    }
}
