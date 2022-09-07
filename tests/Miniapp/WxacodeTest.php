<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Miniapp;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WxacodeTest extends TestCase
{
    protected ?Wxacode $request = null;

    protected function setUp(): void
    {
        $this->request = new Wxacode();
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
            'env_version',
            'width',
            'is_hyaline',
            'line_color',
            'line_color_value',
            'auto_color',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'env_version' => null,
            'width' => null,
            'is_hyaline' => null,
            'line_color' => null,
            'line_color_value' => null,
            'auto_color' => null,
            'token' => 'foo',
            'path' => '/bar',
        ], $resolver->resolve(['token' => 'foo', 'path' => '/bar']));

        static::assertSame([
            'env_version' => 'develop',
            'width' => 240,
            'is_hyaline' => true,
            'line_color' => '#FFB6C1',
            'line_color_value' => ['r' => 255, 'g' => 182, 'b' => 193],
            'auto_color' => false,
            'token' => 'foo',
            'path' => '/bar',
        ], $resolver->resolve([
            'token' => 'foo',
            'path' => '/bar',
            'env_version' => 'develop',
            'width' => 240,
            'is_hyaline' => true,
            'line_color' => '#FFB6C1',
        ]));

        $resolved = $resolver->resolve([
            'token' => 'foo',
            'path' => '/bar',
            'line_color_value' => ['r' => 255, 'g' => 0, 'b' => 0],
        ]);

        // auto_color=false when line_color or line_color_value has been setting.
        static::assertSame(['r' => 255, 'g' => 0, 'b' => 0], $resolved['line_color_value']);
        static::assertFalse($resolved['auto_color']);
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['token' => 'foo', 'path' => '/bar']);

        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Wxacode::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'path' => '/bar',
            ],
        ], $requestOptions->toArray());

        $requestOptions = $this->request->build([
            'token' => 'foo',
            'path' => '/bar',
            'env_version' => 'develop',
            'width' => 240,
            'is_hyaline' => true,
            'line_color' => '#FFB6C1',
        ]);

        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'path' => '/bar',
                'env_version' => 'develop',
                'width' => 240,
                'auto_color' => false,
                'is_hyaline' => true,
                'line_color' => ['r' => 255, 'g' => 182, 'b' => 193],
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

        $result = (new Wxacode($client))->send(['token' => 'foo', 'path' => '/bar']);
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

        (new Wxacode($client))->send(['token' => 'foo', 'path' => '/bar']);
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
        $this->expectExceptionMessage('The required option "token" is missing');

        $this->request->build(['path' => '/bar']);
    }

    public function testPathInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "path" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['token' => 'foo', 'path' => 123]);
    }

    public function testEnvVersionInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "env_version" with value "foo" is invalid. Accepted values are: null, "release", "trial", "develop"');

        $this->request->build(['token' => 'foo', 'path' => '/bar', 'env_version' => 'foo']);
    }

    public function testWidthInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "width" with value "test" is expected to be of type "null" or "int", but is of type "string"');

        $this->request->build(['token' => 'foo', 'path' => '/bar', 'width' => 'test']);
    }

    public function testIsHyalineInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "is_hyaline" with value "test" is expected to be of type "null" or "bool", but is of type "string"');

        $this->request->build(['token' => 'foo', 'path' => '/bar', 'is_hyaline' => 'test']);
    }

    public function testLineColorInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "line_color" with value 123 is expected to be of type "null" or "string", but is of type "int"');

        $this->request->build(['token' => 'foo', 'path' => '/bar', 'line_color' => 123]);
    }

    public function testLineColorFormatInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "line_color" with value "test" is invalid');

        $this->request->build(['token' => 'foo', 'path' => '/bar', 'line_color' => 'test']);
    }

    public function testLineColorValueInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "line_color_value" with value 123 is expected to be of type "null" or "array", but is of type "int"');

        $this->request->build(['token' => 'foo', 'path' => '/bar', 'line_color_value' => 123]);
    }

    public function testAutoColorInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "auto_color" with value 123 is expected to be of type "null" or "bool", but is of type "int"');

        $this->request->build(['token' => 'foo', 'path' => '/bar', 'auto_color' => 123]);
    }
}
