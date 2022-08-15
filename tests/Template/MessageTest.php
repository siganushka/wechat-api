<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Template;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Template\Template;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageTest extends TestCase
{
    private ?Message $request = null;

    protected function setUp(): void
    {
        $this->request = new Message();
    }

    protected function tearDown(): void
    {
        $this->request = null;
    }

    public function testDefinedOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertEquals([
            'token',
            'touser',
            'template',
            'url',
            'miniprogram',
            'client_msg_id',
        ], $resolver->getDefinedOptions());
    }

    public function testResolve(): void
    {
        $template = new Template('baz');
        $resolved = $this->request->resolve(['token' => 'foo', 'touser' => 'bar', 'template' => $template]);
        static::assertNull($resolved['url']);
        static::assertEquals([], $resolved['miniprogram']);
        static::assertSame('bar', $resolved['touser']);
        static::assertSame($template, $resolved['template']);
    }

    public function testBuild(): void
    {
        $template = new Template('baz');
        $requestOptions = $this->request->build(['token' => 'foo', 'touser' => 'bar', 'template' => $template]);

        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Message::URL, $requestOptions->getUrl());
        static::assertEquals([
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
            'url' => '/foo',
            'miniprogram' => [
                'appid' => 'test_appid',
                'pagepath' => 'test_pagepath',
            ],
        ]);

        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'touser' => 'bar',
                'template_id' => 'baz',
                'data' => [
                    'key1' => [
                        'value' => 'key1_value',
                    ],
                    'key2' => [
                        'value' => 'key2_value',
                        'color' => '#ff0000',
                    ],
                ],
                'url' => '/foo',
                'miniprogram' => [
                    'appid' => 'test_appid',
                    'pagepath' => 'test_pagepath',
                ],
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

    public function testTemplateInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage(sprintf('The option "template" with value "baz" is expected to be of type "%s", but is of type "string"', Template::class));

        $this->request->resolve(['token' => 'foo', 'touser' => 'bar', 'template' => 'baz']);
    }
}
