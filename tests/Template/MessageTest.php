<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Template;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Template\Template;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MessageTest extends TestCase
{
    public function testResolve(): void
    {
        $request = static::createRequest();

        $template = new Template('baz');
        $resolved = $request->resolve(['access_token' => 'foo', 'touser' => 'bar', 'template' => $template]);
        static::assertNull($resolved['url']);
        static::assertSame([], $resolved['miniprogram']);
        static::assertSame('foo', $resolved['access_token']);
        static::assertSame('bar', $resolved['touser']);
        static::assertSame($template, $resolved['template']);
        static::assertSame(['access_token', 'touser', 'template', 'url', 'miniprogram'], $request->getResolver()->getDefinedOptions());
    }

    public function testSend(): void
    {
        $data = [
            'msgid' => 1024,
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $request = static::createRequest();
        $request->setHttpClient($httpClient);

        $template = new Template('baz');
        $parsedResponse = $request->send(['access_token' => 'foo', 'touser' => 'bar', 'template' => $template]);
        static::assertSame($data, $parsedResponse);
    }

    public function testConfigureRequest(): void
    {
        $request = static::createRequest();
        $requestOptions = new RequestOptions();

        $template = new Template('baz');
        $configureRequestRef = new \ReflectionMethod($request, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($request, $requestOptions, $request->resolve(['access_token' => 'foo', 'touser' => 'bar', 'template' => $template]));

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

        $customOptions = [
            'access_token' => 'foo',
            'touser' => 'bar',
            'template' => $template,
            'url' => '/foo',
            'miniprogram' => [
                'appid' => 'test_appid',
                'pagepath' => 'test_pagepath',
            ],
        ];

        $configureRequestRef->invoke($request, $requestOptions, $request->resolve($customOptions));
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

        $request = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($request, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($request, $response);
    }

    public function testAccessTokenMissingException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "access_token" is missing');

        $template = new Template('baz');
        $request = static::createRequest();
        $request->resolve(['touser' => 'bar', 'template' => $template]);
    }

    public function testAccessTokenInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "access_token" with value 123 is expected to be of type "string", but is of type "int"');

        $template = new Template('baz');
        $request = static::createRequest();
        $request->resolve(['access_token' => 123, 'touser' => 'bar', 'template' => $template]);
    }

    public function testTemplateInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage(sprintf('The option "template" with value "baz" is expected to be of type "%s", but is of type "string"', Template::class));

        $request = static::createRequest();
        $request->resolve(['access_token' => 'foo', 'touser' => 'bar', 'template' => 'baz']);
    }

    public static function createRequest(): Message
    {
        return new Message();
    }
}
