<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Miniapp;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Test\RequestTestCase;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QrcodeTest extends RequestTestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertSame([
            'token',
            'path',
            'width',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'width' => null,
            'token' => 'foo',
            'path' => '/bar',
        ], $resolver->resolve(['token' => 'foo', 'path' => '/bar']));

        static::assertSame([
            'width' => 240,
            'token' => 'foo',
            'path' => '/bar',
        ], $resolver->resolve(['token' => 'foo', 'path' => '/bar', 'width' => 240]));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['token' => 'foo', 'path' => '/bar']);
        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Qrcode::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'path' => '/bar',
            ],
        ], $requestOptions->toArray());

        $requestOptions = $this->request->build(['token' => 'foo', 'path' => '/bar', 'width' => 240]);
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'path' => '/bar',
                'width' => 240,
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
        $client = new MockHttpClient($response);

        $result = $this->request->setHttpClient($client)->send(['token' => 'foo', 'path' => '/bar']);
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

        $parseResponseRef = new \ReflectionMethod($this->request, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($this->request, $response);
    }

    public function testTokenMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "token" is missing');

        $this->request->build(['path' => '/bar']);
    }

    public function testTokenInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "token" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['token' => 123, 'path' => '/bar']);
    }

    public function testPathMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "path" is missing');

        $this->request->build(['token' => 'foo']);
    }

    public function testPathInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "path" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['token' => 'foo', 'path' => 123]);
    }

    public function testWidthInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "width" with value "test" is expected to be of type "null" or "int", but is of type "string"');

        $this->request->build(['token' => 'foo', 'path' => '/bar', 'width' => 'test']);
    }

    protected function createRequest(): Qrcode
    {
        return new Qrcode();
    }
}
