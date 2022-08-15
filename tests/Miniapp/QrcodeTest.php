<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Miniapp;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class QrcodeTest extends TestCase
{
    public function testResolve(): void
    {
        $request = $this->createRequest();

        $resolved = $request->resolve(['path' => '/test']);
        static::assertEquals([
            'path' => '/test',
        ], $resolved);
    }

    public function testBuild(): void
    {
        $request = $this->createRequest();
        $requestOptions = $request->build(['path' => '/test']);

        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Qrcode::URL, $requestOptions->getUrl());

        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'path' => '/test',
            ],
        ], $requestOptions->toArray());

        $requestOptions = $request->build([
            'path' => '/index/index',
            'width' => 320,
        ]);

        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'path' => '/index/index',
                'width' => 320,
            ],
        ], $requestOptions->toArray());
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
        $httpClient = new MockHttpClient($response);

        $request = $this->createRequest();
        $request->setHttpClient($httpClient);

        $result = $request->send(['path' => '/test']);
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

        $info = [
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ];

        $response = ResponseFactory::createMockResponseWithJson($data, $info);

        $request = $this->createRequest();
        $parseResponseRef = new \ReflectionMethod($request, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($request, $response);
    }

    public function testPathMissingException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "path" is missing');

        $request = $this->createRequest();
        $request->resolve();
    }

    public function testPathInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "path" with value 123 is expected to be of type "string", but is of type "int"');

        $request = $this->createRequest();
        $request->resolve(['path' => 123]);
    }

    protected function createRequest(): Qrcode
    {
        $accessToken = $this->createMockAccessToken();

        return new Qrcode($accessToken);
    }
}
