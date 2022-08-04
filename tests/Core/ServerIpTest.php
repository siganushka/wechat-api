<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Siganushka\ApiClient\Wechat\Tests\BaseTest;
use Symfony\Component\HttpClient\MockHttpClient;

class ServerIpTest extends BaseTest
{
    public function testResolve(): void
    {
        $request = $this->createRequest();

        $resolved = $request->resolve();
        static::assertSame([], $resolved);
    }

    public function testBuild(): void
    {
        $request = $this->createRequest();
        $requestOptions = $request->build();

        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(ServerIp::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = [
            'ip_list' => ['foo', 'bar', 'baz'],
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);
        $httpClient = new MockHttpClient($response);

        $request = $this->createRequest();
        $request->setHttpClient($httpClient);

        $result = $request->send();
        static::assertSame($data['ip_list'], $result);
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

        $request = $this->createRequest();
        $parseResponseRef = new \ReflectionMethod($request, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($request, $response);
    }

    protected function createRequest(): ServerIp
    {
        $accessToken = $this->createMockAccessToken();

        return new ServerIp($accessToken);
    }
}
