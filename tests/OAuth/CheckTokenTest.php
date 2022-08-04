<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\OAuth\CheckToken;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CheckTokenTest extends TestCase
{
    public function testResolve(): void
    {
        $request = static::createRequest();

        $resolved = $request->resolve(['access_token' => 'foo', 'openid' => 'bar']);
        static::assertSame('foo', $resolved['access_token']);
        static::assertSame('bar', $resolved['openid']);
    }

    public function testBuild(): void
    {
        $request = static::createRequest();
        $requestOptions = $request->build(['access_token' => 'foo', 'openid' => 'bar']);

        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(CheckToken::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
                'openid' => 'bar',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = [
            'errcode' => 0,
            'errmsg' => 'ok',
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);
        $httpClient = new MockHttpClient($response);

        $request = static::createRequest();
        $request->setHttpClient($httpClient);

        $result = $request->send(['access_token' => 'foo', 'openid' => 'bar']);
        static::assertSame($data, $result);
    }

    public function testParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(16);
        $this->expectExceptionMessage('test error');

        $data = [
            'errcode' => 16,
            'errmsg' => 'test error',
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);

        $request = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($request, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($request, $response);
    }

    public function testAccessTokenMissingException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "access_token" is missing');

        $request = static::createRequest();
        $request->resolve(['openid' => 'bar']);
    }

    public function testOpenidMissingException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "openid" is missing');

        $request = static::createRequest();
        $request->resolve(['access_token' => 'foo']);
    }

    public static function createRequest(): CheckToken
    {
        return new CheckToken();
    }
}
