<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RefreshTokenTest extends TestCase
{
    public function testResolve(): void
    {
        $request = static::createRequest();

        $resolved = $request->resolve(['refresh_token' => 'foo']);
        static::assertSame('foo', $resolved['refresh_token']);
        static::assertFalse($resolved['using_open_api']);
        static::assertSame(['refresh_token', 'using_open_api'], $request->getResolver()->getDefinedOptions());
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

        $request = static::createRequest();
        $request->setHttpClient($httpClient);

        $parsedResponse = $request->send(['refresh_token' => 'foo']);
        static::assertSame($data, $parsedResponse);
    }

    public function testConfigureRequest(): void
    {
        $request = static::createRequest();
        $requestOptions = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($request, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($request, $requestOptions, $request->resolve(['refresh_token' => 'foo']));

        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(RefreshToken::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'appid' => 'test_appid',
                'refresh_token' => 'foo',
                'grant_type' => 'refresh_token',
            ],
        ], $requestOptions->toArray());

        $configureRequestRef->invoke($request, $requestOptions, $request->resolve(['refresh_token' => 'foo', 'using_open_api' => true]));
        static::assertSame([
            'query' => [
                'appid' => 'test_open_appid',
                'refresh_token' => 'foo',
                'grant_type' => 'refresh_token',
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

    public function testRefreshTokenMissingException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "refresh_token" is missing');

        $request = static::createRequest();
        $request->resolve();
    }

    public function testRefreshTokenInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "refresh_token" with value 123 is expected to be of type "string", but is of type "int"');

        $request = static::createRequest();
        $request->resolve(['refresh_token' => 123]);
    }

    public function testUsingOpenApiOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "using_open_api" with value 1 is expected to be of type "bool", but is of type "int"');

        $request = static::createRequest();
        $request->resolve(['refresh_token' => 'bar', 'using_open_api' => 1]);
    }

    public static function createRequest(Configuration $configuration = null): RefreshToken
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::createConfiguration();
        }

        return new RefreshToken($configuration);
    }
}
