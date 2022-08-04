<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;

class AccessTokenTest extends TestCase
{
    public function testResolve(): void
    {
        $request = static::createRequest();

        $resolved = $request->resolve(['code' => 'foo']);
        static::assertSame('foo', $resolved['code']);
        static::assertFalse($resolved['using_open_api']);
    }

    public function testBuild(): void
    {
        $request = static::createRequest();
        $requestOptions = $request->build(['code' => 'foo']);

        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(AccessToken::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'appid' => 'test_appid',
                'secret' => 'test_secret',
                'grant_type' => 'authorization_code',
                'code' => 'foo',
            ],
        ], $requestOptions->toArray());

        $requestOptions = $request->build(['code' => 'foo', 'using_open_api' => true]);
        static::assertSame([
            'query' => [
                'appid' => 'test_open_appid',
                'secret' => 'test_open_secret',
                'grant_type' => 'authorization_code',
                'code' => 'foo',
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

        $response = ResponseFactory::createMockResponseWithJson($data);
        $httpClient = new MockHttpClient($response);

        $request = static::createRequest();
        $request->setHttpClient($httpClient);

        $result = $request->send(['code' => 'foo']);
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

    public function testCodeMissingException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "code" is missing');

        $request = static::createRequest();
        $request->resolve();
    }

    public function testCodeInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "code" with value 123 is expected to be of type "string", but is of type "int"');

        $request = static::createRequest();
        $request->resolve(['code' => 123]);
    }

    public function testOpenAppidNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "open_appid" option');

        $configuration = new Configuration([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
        ]);

        $request = static::createRequest($configuration);
        $requestOptions = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($request, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($request, $requestOptions, $request->resolve(['code' => 'foo', 'using_open_api' => true]));
    }

    public function testOpenSecretNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "open_secret" option');

        $configuration = new Configuration([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'open_appid' => 'test_open_appid',
        ]);

        $request = static::createRequest($configuration);
        $requestOptions = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($request, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($request, $requestOptions, $request->resolve(['code' => 'foo', 'using_open_api' => true]));
    }

    public function testUsingOpenApiOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "using_open_api" with value 1 is expected to be of type "bool", but is of type "int"');

        $request = static::createRequest();
        $request->resolve(['code' => 'bar', 'using_open_api' => 1]);
    }

    public static function createRequest(Configuration $configuration = null): AccessToken
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::createConfiguration();
        }

        return new AccessToken($configuration);
    }
}
