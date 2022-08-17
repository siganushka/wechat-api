<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Template;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Test\RequestTestCase;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Template\Template;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageTest extends RequestTestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertSame([
            'token',
            'touser',
            'template',
            'url',
            'miniprogram',
            'client_msg_id',
        ], $resolver->getDefinedOptions());

        $template = new Template('baz');

        static::assertSame([
            'url' => null,
            'miniprogram' => [],
            'client_msg_id' => null,
            'token' => 'foo',
            'touser' => 'bar',
            'template' => $template,
        ], $resolver->resolve([
            'token' => 'foo',
            'touser' => 'bar',
            'template' => $template,
        ]));

        static::assertSame([
            'url' => '/baz',
            'miniprogram' => [
                'appid' => 'foo',
                'pagepath' => '/bar',
            ],
            'client_msg_id' => 'test_client_msg_id',
            'token' => 'foo',
            'touser' => 'bar',
            'template' => $template,
        ], $resolver->resolve([
            'token' => 'foo',
            'touser' => 'bar',
            'template' => $template,
            'url' => '/baz',
            'miniprogram' => [
                'appid' => 'foo',
                'pagepath' => '/bar',
            ],
            'client_msg_id' => 'test_client_msg_id',
        ]));
    }

    public function testBuild(): void
    {
        $template = new Template('baz');
        $requestOptions = $this->request->build(['token' => 'foo', 'touser' => 'bar', 'template' => $template]);

        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Message::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'touser' => 'bar',
                'template_id' => 'baz',
            ],
        ], $requestOptions->toArray());

        $template->addData('key1', 'key1_value');
        $template->addData('key2', 'key2_value', '#ff0000');

        $requestOptions = $this->request->build([
            'token' => 'foo',
            'touser' => 'bar',
            'template' => $template,
            'url' => '/baz',
            'miniprogram' => [
                'appid' => 'foo',
                'pagepath' => '/bar',
            ],
            'client_msg_id' => 'test_client_msg_id',
        ]);

        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'touser' => 'bar',
                'template_id' => 'baz',
                'url' => '/baz',
                'miniprogram' => [
                    'appid' => 'foo',
                    'pagepath' => '/bar',
                ],
                'data' => [
                    'key1' => [
                        'value' => 'key1_value',
                    ],
                    'key2' => [
                        'value' => 'key2_value',
                        'color' => '#ff0000',
                    ],
                ],
                'client_msg_id' => 'test_client_msg_id',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = [
            'msgid' => 1024,
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);
        $client = new MockHttpClient($response);

        $template = new Template('baz');
        $result = $this->request->send($client, ['token' => 'foo', 'touser' => 'bar', 'template' => $template]);
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

        $template = new Template('baz');
        $this->request->build(['touser' => 'bar', 'template' => $template]);
    }

    public function testTokenInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "token" with value 123 is expected to be of type "string", but is of type "int"');

        $template = new Template('baz');
        $this->request->build(['token' => 123, 'touser' => 'bar', 'template' => $template]);
    }

    public function testTouserMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "touser" is missing');

        $template = new Template('baz');
        $this->request->build(['token' => 'foo', 'template' => $template]);
    }

    public function testTouserInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "touser" with value 123 is expected to be of type "string", but is of type "int"');

        $template = new Template('baz');
        $this->request->build(['token' => 'foo', 'touser' => 123, 'template' => $template]);
    }

    public function testTemplateInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage(sprintf('The option "template" with value "baz" is expected to be of type "%s", but is of type "string"', Template::class));

        $this->request->build(['token' => 'foo', 'touser' => 'bar', 'template' => 'baz']);
    }

    protected function createRequest(): Message
    {
        return new Message();
    }
}
