<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AccessTokenTest extends TestCase
{
    public function testResolve(): void
    {
        $accessToken = static::createRequest();

        $resolved = $accessToken->resolve();
        static::assertSame([], $resolved);
        static::assertSame([], $accessToken->getResolver()->getDefinedOptions());
    }

    public function testSend(): void
    {
        $data = [
            'access_token' => 'foo',
            'expires_in' => 1024,
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $accessToken = static::createRequest();
        $accessToken->setHttpClient($httpClient);

        $parsedResponse = $accessToken->send();
        static::assertSame($data, $parsedResponse);
    }

    public function testConfigureRequest(): void
    {
        $accessToken = static::createRequest();
        $request = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($accessToken, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($accessToken, $request, $accessToken->resolve());

        static::assertSame('GET', $request->getMethod());
        static::assertSame(AccessToken::URL, $request->getUrl());
        static::assertSame([
            'query' => [
                'appid' => 'test_appid',
                'secret' => 'test_secret',
                'grant_type' => 'client_credential',
            ],
        ], $request->toArray());
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

        $accessToken = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($accessToken, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($accessToken, $response);
    }

    public static function createRequest(): AccessToken
    {
        $cachePool = new FilesystemAdapter();
        $cachePool->clear();

        $configuration = ConfigurationTest::createConfiguration();

        return new AccessToken($cachePool, $configuration);
    }
}
