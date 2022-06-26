<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Miniapp;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WxacodeTest extends TestCase
{
    public function testResolve(): void
    {
        $request = static::createRequest();

        $resolved = $request->resolve(['access_token' => 'foo', 'path' => '/test']);
        static::assertSame('foo', $resolved['access_token']);
        static::assertSame('/test', $resolved['path']);
        static::assertSame([], $resolved['line_color']);
    }

    public function testSend(): void
    {
        $data = 'bin_content';

        $response = ResponseFactory::createMockResponse($data);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $request = static::createRequest();
        $request->setHttpClient($httpClient);

        $parsedResponse = $request->send(['access_token' => 'foo', 'path' => '/test']);
        static::assertSame($data, $parsedResponse);
    }

    public function testConfigureRequest(): void
    {
        $request = static::createRequest();
        $requestOptions = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($request, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($request, $requestOptions, $request->resolve(['access_token' => 'foo', 'path' => '/test']));

        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Wxacode::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'path' => '/test',
            ],
        ], $requestOptions->toArray());

        $customOptions = [
            'access_token' => 'bar',
            'path' => '/index/index',
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
                'path' => '/index/index',
                'env_version' => 'develop',
                'line_color' => ['r' => 255, 'g' => 255, 'b' => 255],
                'width' => 320,
                'auto_color' => true,
                'is_hyaline' => true,
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
        $request->resolve(['path' => '/test']);
    }

    public function testPathMissingException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "path" is missing');

        $request = static::createRequest();
        $request->resolve(['access_token' => 'foo']);
    }

    public function testPathInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "path" with value 123 is expected to be of type "string", but is of type "int"');

        $request = static::createRequest();
        $request->resolve(['access_token' => 'foo', 'path' => 123]);
    }

    public function testLineColorInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The nested option "line_color" with value 123 is expected to be of type array, but is of type "int"');

        $request = static::createRequest();
        $request->resolve([
            'access_token' => 'foo',
            'path' => '/test',
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
            'path' => '/test',
            'env_version' => 'foo',
        ]);
    }

    public static function createRequest(): Wxacode
    {
        return new Wxacode();
    }
}
