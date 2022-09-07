<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessTokenTest extends TestCase
{
    protected ?AccessToken $request = null;

    protected function setUp(): void
    {
        $this->request = new AccessToken();
    }

    protected function tearDown(): void
    {
        $this->request = null;
    }

    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertSame([
            'appid',
            'secret',
            'code',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'appid' => 'foo',
            'secret' => 'bar',
            'code' => 'baz',
        ], $resolver->resolve(['appid' => 'foo', 'secret' => 'bar', 'code' => 'baz']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['appid' => 'foo', 'secret' => 'bar', 'code' => 'baz']);

        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(AccessToken::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'appid' => 'foo',
                'secret' => 'bar',
                'grant_type' => 'authorization_code',
                'code' => 'baz',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = [
            'access_token' => 'foo',
            'expires_in' => 12,
            'refresh_token' => 'test_refresh_token',
            'openid' => 'test_openid',
            'scope' => 'test_scope',
        ];

        $body = json_encode($data);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $result = (new AccessToken($client))->send(['appid' => 'foo', 'secret' => 'bar', 'code' => 'baz']);
        static::assertSame($data, $result);
    }

    public function testSendWithParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(16);
        $this->expectExceptionMessage('test error');

        $data = [
            'errcode' => 16,
            'errmsg' => 'test error',
        ];

        $body = json_encode($data);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $cachePool = new NullAdapter();

        (new AccessToken($client, $cachePool))->send(['appid' => 'foo', 'secret' => 'bar', 'code' => 'baz']);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        $this->request->build(['secret' => 'bar', 'code' => 'baz']);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['appid' => 123, 'secret' => 'bar', 'code' => 'baz']);
    }

    public function testSecretMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "secret" is missing');

        $this->request->build(['appid' => 'foo', 'code' => 'baz']);
    }

    public function testSecretInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "secret" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['appid' => 'foo', 'secret' => 123, 'code' => 'baz']);
    }

    public function testCodeMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "code" is missing');

        $this->request->build(['appid' => 'foo', 'secret' => 'bar']);
    }

    public function testCodeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "code" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['appid' => 'foo', 'secret' => 'bar', 'code' => 123]);
    }
}
