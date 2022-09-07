<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RefreshTokenTest extends TestCase
{
    protected ?RefreshToken $request = null;

    protected function setUp(): void
    {
        $this->request = new RefreshToken();
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
            'refresh_token',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'appid' => 'foo',
            'refresh_token' => 'bar',
        ], $resolver->resolve(['appid' => 'foo', 'refresh_token' => 'bar']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['appid' => 'foo', 'refresh_token' => 'bar']);

        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(RefreshToken::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'appid' => 'foo',
                'refresh_token' => 'bar',
                'grant_type' => 'refresh_token',
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

        $result = (new RefreshToken($client))->send(['appid' => 'foo', 'refresh_token' => 'bar']);
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

        (new RefreshToken($client))->send(['appid' => 'foo', 'refresh_token' => 'bar']);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        $this->request->build(['refresh_token' => 'bar']);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['appid' => 123, 'refresh_token' => 'bar']);
    }

    public function testRefreshTokenMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "refresh_token" is missing');

        $this->request->build(['appid' => 'foo']);
    }

    public function testRefreshTokenInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "refresh_token" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['appid' => 'foo', 'refresh_token' => 123]);
    }
}
