<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\OAuth\UserInfo;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class UserInfoTest extends TestCase
{
    public function testResolve(): void
    {
        $request = static::createRequest();

        $resolved = $request->resolve(['access_token' => 'foo', 'openid' => 'bar']);
        static::assertSame('foo', $resolved['access_token']);
        static::assertSame('bar', $resolved['openid']);
        static::assertSame('zh_CN', $resolved['lang']);
    }

    public function testBuild(): void
    {
        $request = static::createRequest();
        $requestOptions = $request->build(['access_token' => 'foo', 'openid' => 'bar']);

        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(UserInfo::URL, $requestOptions->getUrl());
        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
                'openid' => 'bar',
                'lang' => 'zh_CN',
            ],
        ], $requestOptions->toArray());

        $requestOptions = $request->build(['access_token' => 'foo', 'openid' => 'bar', 'lang' => 'en_US']);
        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
                'openid' => 'bar',
                'lang' => 'en_US',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = [
            'openid' => 'test_openid',
            'nickname' => 'test_nickname',
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

    public function testAccessTokenInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "access_token" with value 123 is expected to be of type "string", but is of type "int"');

        $request = static::createRequest();
        $request->resolve(['access_token' => 123, 'openid' => 'bar']);
    }

    public static function createRequest(): UserInfo
    {
        return new UserInfo();
    }
}
