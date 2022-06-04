<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Miniapp;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SessionKeyTest extends TestCase
{
    public function testResolve(): void
    {
        $sessionKey = static::createRequest();

        $resolved = $sessionKey->resolve(['code' => 'foo']);
        static::assertSame(['code' => 'foo'], $resolved);
        static::assertSame(['code'], $sessionKey->getResolver()->getDefinedOptions());
    }

    public function testSend(): void
    {
        $data = [
            'openid' => 'foo',
            'session_key' => 'bar',
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $sessionKey = static::createRequest();
        $sessionKey->setHttpClient($httpClient);

        $parsedResponse = $sessionKey->send(['code' => 'foo']);
        static::assertSame($data, $parsedResponse);
    }

    public function testConfigureRequest(): void
    {
        $sessionKey = static::createRequest();
        $request = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($sessionKey, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($sessionKey, $request, $sessionKey->resolve(['code' => 'foo']));

        static::assertSame('GET', $request->getMethod());
        static::assertSame(SessionKey::URL, $request->getUrl());
        static::assertSame([
            'query' => [
                'appid' => 'test_appid',
                'secret' => 'test_secret',
                'grant_type' => 'authorization_code',
                'code' => 'foo',
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

        $sessionKey = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($sessionKey, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($sessionKey, $response);
    }

    public function testCodeMissingException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "code" is missing');

        $sessionKey = static::createRequest();
        $sessionKey->resolve();
    }

    public function testCodeInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "code" with value 123 is expected to be of type "string", but is of type "int"');

        $sessionKey = static::createRequest();
        $sessionKey->resolve(['code' => 123]);
    }

    public static function createRequest(): SessionKey
    {
        $cachePool = new FilesystemAdapter();
        $cachePool->clear();

        $configuration = ConfigurationTest::createConfiguration();

        return new SessionKey($cachePool, $configuration);
    }
}
