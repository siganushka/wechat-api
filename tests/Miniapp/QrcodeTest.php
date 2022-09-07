<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Miniapp;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QrcodeTest extends TestCase
{
    protected ?Qrcode $request = null;

    protected function setUp(): void
    {
        $this->request = new Qrcode();
    }

    protected function tearDown(): void
    {
        $this->request = null;
    }

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
        $body = 'bin_content';
        $info = [
            'response_headers' => [
                'Content-Type' => 'image/png',
            ],
        ];

        $mockResponse = new MockResponse($body, $info);
        $client = new MockHttpClient($mockResponse);

        $result = (new Qrcode($client))->send(['token' => 'foo', 'path' => '/bar']);
        static::assertSame($body, $result);
    }

    public function testSendWithParseResponseException(): void
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

        $body = json_encode($data);

        $mockResponse = new MockResponse($body, $info);
        $client = new MockHttpClient($mockResponse);

        (new Qrcode($client))->send(['token' => 'foo', 'path' => '/bar']);
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
}
