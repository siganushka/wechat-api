<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\Wechat\OAuth\RefreshToken;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class RefreshTokenTest extends TestCase
{
    protected RefreshToken $request;

    protected function setUp(): void
    {
        $this->request = new RefreshToken();
    }

    public function testResolve(): void
    {
        static::assertEquals([
            'appid' => 'foo',
            'refresh_token' => 'bar',
        ], $this->request->resolve(['appid' => 'foo', 'refresh_token' => 'bar']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['appid' => 'foo', 'refresh_token' => 'bar']);

        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(RefreshToken::URL, $requestOptions->getUrl());
        static::assertEquals([
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

        $body = json_encode($data, \JSON_THROW_ON_ERROR);

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

        $body = json_encode($data, \JSON_THROW_ON_ERROR);

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
