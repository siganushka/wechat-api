<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Miniapp;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Siganushka\ApiClient\Wechat\Tests\BaseTest;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class WxacodeTest extends BaseTest
{
    public function testResolve(): void
    {
        $request = $this->createRequest();

        $resolved = $request->resolve(['path' => '/test']);
        static::assertSame([
            'line_color' => [],
            'path' => '/test',
        ], $resolved);
    }

    public function testBuild(): void
    {
        $request = $this->createRequest();
        $requestOptions = $request->build(['path' => '/test']);

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

        $requestOptions = $request->build([
            'path' => '/index/index',
            'env_version' => 'develop',
            'width' => 320,
            'auto_color' => true,
            'is_hyaline' => true,
            'line_color' => ['r' => 255, 'g' => 255, 'b' => 255],
        ]);

        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'path' => '/index/index',
                'env_version' => 'develop',
                'width' => 320,
                'auto_color' => true,
                'is_hyaline' => true,
                'line_color' => ['r' => 255, 'g' => 255, 'b' => 255],
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

    public function testLineColorInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The nested option "line_color" with value 123 is expected to be of type array, but is of type "int"');

        $request = $this->createRequest();
        $request->resolve([
            'path' => '/test',
            'line_color' => 123,
        ]);
    }

    public function testEnvVersionInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "env_version" with value "foo" is invalid. Accepted values are: "release", "trial", "develop');

        $request = $this->createRequest();
        $request->resolve([
            'path' => '/test',
            'env_version' => 'foo',
        ]);
    }

    protected function createRequest(): Wxacode
    {
        $accessToken = $this->createMockAccessToken();

        return new Wxacode($accessToken);
    }
}
