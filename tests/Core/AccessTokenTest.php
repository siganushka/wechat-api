<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\MockHttpClient;

class AccessTokenTest extends TestCase
{
    public function testResolve(): void
    {
        $request = static::createRequest();

        $resolved = $request->resolve();
        static::assertSame([], $resolved);
    }

    public function testBuild(): void
    {
        $request = static::createRequest();
        $requestOptions = $request->build();

        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(AccessToken::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'appid' => 'test_appid',
                'secret' => 'test_secret',
                'grant_type' => 'client_credential',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = [
            'access_token' => 'foo',
            'expires_in' => 1024,
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);
        $httpClient = new MockHttpClient($response);

        $request = static::createRequest();
        $request->setHttpClient($httpClient);

        $result = $request->send();
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

    public static function createRequest(): AccessToken
    {
        $cachePool = new FilesystemAdapter();
        $cachePool->clear();

        $configuration = ConfigurationTest::createConfiguration();

        return new AccessToken($cachePool, $configuration);
    }
}
