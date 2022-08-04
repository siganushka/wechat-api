<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Ticket;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Tests\BaseTest;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class TicketTest extends BaseTest
{
    public function testResolve(): void
    {
        $request = $this->createRequest();

        $resolved = $request->resolve();
        static::assertSame(['type' => 'jsapi'], $resolved);
    }

    public function testBuild(): void
    {
        $request = $this->createRequest();
        $requestOptions = $request->build();

        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(Ticket::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
                'type' => 'jsapi',
            ],
        ], $requestOptions->toArray());

        $requestOptions = $request->build(['type' => 'wx_card']);
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
                'type' => 'wx_card',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = [
            'ticket' => 'test_ticket',
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);
        $httpClient = new MockHttpClient($response);

        $request = $this->createRequest();
        $request->setHttpClient($httpClient);

        $result = $request->send();
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

        $request = $this->createRequest();
        $parseResponseRef = new \ReflectionMethod($request, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($request, $response);
    }

    public function testTypeInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "type" with value "bar" is invalid. Accepted values are: "jsapi", "wx_card"');

        $request = $this->createRequest();
        $request->resolve(['type' => 'bar']);
    }

    protected function createRequest(): Ticket
    {
        $cachePool = new FilesystemAdapter();
        $cachePool->clear();

        $accessToken = $this->createMockAccessToken();

        return new Ticket($cachePool, $accessToken);
    }
}
