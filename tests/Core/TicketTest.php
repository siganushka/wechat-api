<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\Wechat\Core\Ticket;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class TicketTest extends TestCase
{
    protected ?Ticket $request = null;

    protected function setUp(): void
    {
        $this->request = new Ticket();
    }

    protected function tearDown(): void
    {
        $this->request = null;
    }

    public function testResolve(): void
    {
        static::assertEquals([
            'type' => 'jsapi',
            'token' => 'foo',
        ], $this->request->resolve(['token' => 'foo']));

        static::assertEquals([
            'type' => 'wx_card',
            'token' => 'foo',
        ], $this->request->resolve(['token' => 'foo', 'type' => 'wx_card']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['token' => 'foo']);
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(Ticket::URL, $requestOptions->getUrl());
        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
                'type' => 'jsapi',
            ],
        ], $requestOptions->toArray());

        $requestOptions = $this->request->build(['token' => 'foo', 'type' => 'wx_card']);
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(Ticket::URL, $requestOptions->getUrl());
        static::assertEquals([
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

        $body = json_encode($data);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $result = (new Ticket($client))->send(['token' => 'foo']);
        static::assertSame($data, $result);
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

        $body = json_encode($data);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $cachePool = new NullAdapter();

        (new Ticket($client, $cachePool))->send(['token' => 'foo']);
    }

    public function testTokenMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "token" is missing');

        $this->request->build();
    }

    public function testTokenInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "token" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['token' => 123]);
    }

    public function testTypeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "type" with value "bar" is invalid. Accepted values are: "jsapi", "wx_card"');

        $this->request->build(['token' => 'foo', 'type' => 'bar']);
    }
}
