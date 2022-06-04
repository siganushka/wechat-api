<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AccessTokenTest extends TestCase
{
    public function testResolve(): void
    {
        $accessToken = static::createRequest();

        $resolved = $accessToken->resolve(['code' => 'foo']);
        static::assertSame('foo', $resolved['code']);
        static::assertFalse($resolved['using_open_api']);
        static::assertSame(['code', 'using_open_api'], $accessToken->getResolver()->getDefinedOptions());
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

        $response = ResponseFactory::createMockResponseWithJson($data);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $accessToken = static::createRequest();
        $accessToken->setHttpClient($httpClient);

        $parsedResponse = $accessToken->send(['code' => 'foo']);
        static::assertSame($data, $parsedResponse);
    }

    public function testConfigureRequest(): void
    {
        $accessToken = static::createRequest();
        $request = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($accessToken, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($accessToken, $request, $accessToken->resolve(['code' => 'foo']));

        static::assertSame('GET', $request->getMethod());
        static::assertSame(AccessToken::URL, $request->getUrl());
        static::assertSame([
            'query' => [
                'appid' => 'test_appid',
                'secret' => 'test_secret',
                'grant_type' => 'authorization_code',
                'code' => 'foo',
            ],
        ], $request->toArray());

        $configureRequestRef->invoke($accessToken, $request, $accessToken->resolve(['code' => 'foo', 'using_open_api' => true]));
        static::assertSame([
            'query' => [
                'appid' => 'test_open_appid',
                'secret' => 'test_open_secret',
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

        $accessToken = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($accessToken, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($accessToken, $response);
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

    public function testUsingOpenApiOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "using_open_api" with value 1 is expected to be of type "bool", but is of type "int"');

        $request = static::createRequest();
        $request->resolve(['code' => 'bar', 'using_open_api' => 1]);
    }

    public static function createRequest(): AccessToken
    {
        $configuration = ConfigurationTest::createConfiguration();

        return new AccessToken($configuration);
    }
}
