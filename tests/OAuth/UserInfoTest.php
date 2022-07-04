<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\OAuth\UserInfo;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserInfoTest extends TestCase
{
    public function testResolve(): void
    {
        $request = static::createRequest();

        $resolved = $request->resolve(['access_token' => 'foo', 'openid' => 'bar']);
        static::assertSame('foo', $resolved['access_token']);
        static::assertSame('bar', $resolved['openid']);
        static::assertSame('zh_CN', $resolved['lang']);
        static::assertSame(['access_token', 'openid', 'lang'], $request->getResolver()->getDefinedOptions());
    }

    public function testSend(): void
    {
        $data = [
            'openid' => 'test_openid',
            'nickname' => 'test_nickname',
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $request = static::createRequest();
        $request->setHttpClient($httpClient);

        $parsedResponse = $request->send(['access_token' => 'foo', 'openid' => 'bar']);
        static::assertSame($data, $parsedResponse);
    }

    public function testConfigureRequest(): void
    {
        $request = static::createRequest();
        $requestOptions = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($request, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($request, $requestOptions, $request->resolve(['access_token' => 'foo', 'openid' => 'bar']));
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(UserInfo::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
                'openid' => 'bar',
                'lang' => 'zh_CN',
            ],
        ], $requestOptions->toArray());

        $configureRequestRef->invoke($request, $requestOptions, $request->resolve(['access_token' => 'foo', 'openid' => 'bar', 'lang' => 'en_US']));
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
                'openid' => 'bar',
                'lang' => 'en_US',
            ],
        ], $requestOptions->toArray());
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
