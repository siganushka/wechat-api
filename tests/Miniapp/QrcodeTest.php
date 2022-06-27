<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Miniapp;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class QrcodeTest extends TestCase
{
    public function testResolve(): void
    {
        $request = static::createRequest();

        $resolved = $request->resolve(['access_token' => 'foo', 'path' => '/test']);
        static::assertSame([
            'access_token' => 'foo',
            'path' => '/test',
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
        static::assertSame(Qrcode::URL, $requestOptions->getUrl());

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
            'width' => 320,
        ];

        $configureRequestRef->invoke($request, $requestOptions, $request->resolve($customOptions));
        static::assertSame([
            'query' => [
                'access_token' => 'bar',
            ],
            'json' => [
                'path' => '/index/index',
                'width' => 320,
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

    public static function createRequest(): Qrcode
    {
        return new Qrcode();
    }
}
