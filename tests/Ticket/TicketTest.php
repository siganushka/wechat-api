<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Ticket;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Tests\Core\TokenOptionsTest;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketTest extends TestCase
{
    private ?Ticket $request = null;

    protected function setUp(): void
    {
        $this->request = new Ticket();
    }

    protected function tearDown(): void
    {
        $this->request = null;
    }

    public function testDefinedOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertContains('token', $resolver->getDefinedOptions());
        static::assertContains('type', $resolver->getDefinedOptions());
    }

    public function testResolve(): void
    {
        $resolved = $this->request->resolve(['token' => 'foo']);
        static::assertArrayNotHasKey('using_config', $resolved);
        static::assertSame('foo', $resolved['token']);
        static::assertSame('jsapi', $resolved['type']);

        $this->request->using(TokenOptionsTest::create());

        $resolved = $this->request->resolve();
        static::assertSame('default', $resolved['using_config']);
        static::assertSame('test_token_1', $resolved['token']);
        static::assertSame('jsapi', $resolved['type']);

        $resolved = $this->request->resolve(['using_config' => 'custom', 'type' => 'wx_card']);
        static::assertSame('custom', $resolved['using_config']);
        static::assertSame('test_token_2', $resolved['token']);
        static::assertSame('wx_card', $resolved['type']);
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

        $this->request->using(TokenOptionsTest::create());

        $requestOptions = $this->request->build(['type' => 'wx_card']);
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(Ticket::URL, $requestOptions->getUrl());
        static::assertEquals([
            'query' => [
                'access_token' => 'test_token_1',
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

    public function testSendWithParseResponseException(): void
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

        $this->request->resolve();
    }

    public function testTokenInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "token" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->resolve(['token' => 123]);
    }

    public function testTypeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "type" with value "bar" is invalid. Accepted values are: "jsapi", "wx_card"');

        $this->request->resolve(['token' => 'foo', 'type' => 'bar']);
    }
}
