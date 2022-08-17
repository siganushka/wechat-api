<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Ticket;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Test\RequestTestCase;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketTest extends RequestTestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertSame([
            'token',
            'type',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'type' => 'jsapi',
            'token' => 'foo',
        ], $resolver->resolve(['token' => 'foo']));

        static::assertSame([
            'type' => 'wx_card',
            'token' => 'foo',
        ], $resolver->resolve(['token' => 'foo', 'type' => 'wx_card']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['token' => 'foo']);
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(Ticket::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
                'type' => 'jsapi',
            ],
        ], $requestOptions->toArray());

        $requestOptions = $this->request->build(['token' => 'foo', 'type' => 'wx_card']);
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(Ticket::URL, $requestOptions->getUrl());
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
        $client = new MockHttpClient($response);

        $result = $this->request->send($client, ['token' => 'foo']);
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

        $parseResponseRef = new \ReflectionMethod($this->request, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($this->request, $response);
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

    protected function createRequest(): Ticket
    {
        return new Ticket();
    }
}
