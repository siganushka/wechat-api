<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Miniapp;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Miniapp\WxacodeUnlimited;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WxacodeUnlimitedTest extends TestCase
{
    public function testResolve(): void
    {
        $request = static::createRequest();

        $resolved = $request->resolve(['access_token' => 'foo', 'scene' => 'foo']);
        static::assertSame([
            'line_color' => [],
            'access_token' => 'foo',
            'scene' => 'foo',
        ], $resolved);
    }

    public function testSend(): void
    {
        $data = 'bin_content';
        $info = [
            'response_headers' => [
                'Content-Type' => 'image/png',
            ],
        ];

        $response = ResponseFactory::createMockResponse($data, $info);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $request = static::createRequest();
        $request->setHttpClient($httpClient);

        $result = $request->send(['access_token' => 'foo', 'scene' => 'foo']);
        static::assertSame($data, $result);
    }

    public function testConfigureRequest(): void
    {
        $request = static::createRequest();
        $requestOptions = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($request, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($request, $requestOptions, $request->resolve(['access_token' => 'foo', 'scene' => 'foo']));

        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(WxacodeUnlimited::URL, $requestOptions->getUrl());

        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'scene' => 'foo',
            ],
        ], $requestOptions->toArray());

        $customOptions = [
            'access_token' => 'bar',
            'scene' => 'foo',
            'page' => '/index/index',
            'check_path' => false,
            'env_version' => 'develop',
            'width' => 320,
            'auto_color' => true,
            'is_hyaline' => true,
            'line_color' => ['r' => 255, 'g' => 255, 'b' => 255],
        ];

        $configureRequestRef->invoke($request, $requestOptions, $request->resolve($customOptions));
        static::assertSame([
            'query' => [
                'access_token' => 'bar',
            ],
            'json' => [
                'scene' => 'foo',
                'page' => '/index/index',
                'check_path' => false,
                'env_version' => 'develop',
                'width' => 320,
                'auto_color' => true,
                'is_hyaline' => true,
                'line_color' => ['r' => 255, 'g' => 255, 'b' => 255],
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

        $info = [
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ];

        $response = ResponseFactory::createMockResponseWithJson($data, $info);

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
        $request->resolve(['scene' => 'foo']);
    }

    public function testPathMissingException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "scene" is missing');

        $request = static::createRequest();
        $request->resolve(['access_token' => 'foo']);
    }

    public function testPathInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "scene" with value 123 is expected to be of type "string", but is of type "int"');

        $request = static::createRequest();
        $request->resolve(['access_token' => 'foo', 'scene' => 123]);
    }

    public function testLineColorInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The nested option "line_color" with value 123 is expected to be of type array, but is of type "int"');

        $request = static::createRequest();
        $request->resolve([
            'access_token' => 'foo',
            'scene' => 'foo',
            'line_color' => 123,
        ]);
    }

    public function testEnvVersionInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "env_version" with value "foo" is invalid. Accepted values are: "release", "trial", "develop');

        $request = static::createRequest();
        $request->resolve([
            'access_token' => 'foo',
            'scene' => 'foo',
            'env_version' => 'foo',
        ]);
    }

    public static function createRequest(): WxacodeUnlimited
    {
        return new WxacodeUnlimited();
    }
}
