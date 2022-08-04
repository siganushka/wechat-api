<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Template;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Template\Template;
use Siganushka\ApiClient\Wechat\Tests\BaseTest;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class MessageTest extends BaseTest
{
    public function testResolve(): void
    {
        $request = $this->createRequest();

        $template = new Template('baz');
        $resolved = $request->resolve(['touser' => 'bar', 'template' => $template]);
        static::assertNull($resolved['url']);
        static::assertSame([], $resolved['miniprogram']);
        static::assertSame('bar', $resolved['touser']);
        static::assertSame($template, $resolved['template']);
    }

    public function testBuild(): void
    {
        $template = new Template('baz');

        $request = $this->createRequest();
        $requestOptions = $request->build(['touser' => 'bar', 'template' => $template]);

        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Message::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'touser' => 'bar',
                'template_id' => 'baz',
                'data' => [],
            ],
        ], $requestOptions->toArray());

        $template->addData('key1', 'key1_value');
        $template->addData('key2', 'key2_value', '#ff0000');

        $requestOptions = $request->build([
            'touser' => 'bar',
            'template' => $template,
            'url' => '/foo',
            'miniprogram' => [
                'appid' => 'test_appid',
                'pagepath' => 'test_pagepath',
            ],
        ]);

        static::assertSame([
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
        $httpClient = new MockHttpClient($response);

        $request = $this->createRequest();
        $request->setHttpClient($httpClient);

        $template = new Template('baz');
        $result = $request->send(['touser' => 'bar', 'template' => $template]);
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

    public function testTemplateInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage(sprintf('The option "template" with value "baz" is expected to be of type "%s", but is of type "string"', Template::class));

        $request = $this->createRequest();
        $request->resolve(['touser' => 'bar', 'template' => 'baz']);
    }

    protected function createRequest(): Message
    {
        $accessToken = $this->createMockAccessToken();

        return new Message($accessToken);
    }
}
