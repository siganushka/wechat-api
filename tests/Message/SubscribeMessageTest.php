<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\Message;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\Wechat\Message\SubscribeMessage;
use Siganushka\ApiFactory\Wechat\Message\Template;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class SubscribeMessageTest extends TestCase
{
    protected SubscribeMessage $request;

    protected function setUp(): void
    {
        $this->request = new SubscribeMessage();
    }

    public function testResolve(): void
    {
        $template = new Template('baz');

        static::assertEquals([
            'miniprogram_state' => 'formal',
            'page' => null,
            'lang' => 'zh_CN',
            'token' => 'foo',
            'touser' => 'bar',
            'template' => $template,
        ], $this->request->resolve([
            'token' => 'foo',
            'touser' => 'bar',
            'template' => $template,
        ]));

        static::assertEquals([
            'miniprogram_state' => 'developer',
            'page' => '/pages/index/index',
            'lang' => 'en_US',
            'token' => 'foo2',
            'touser' => 'bar2',
            'template' => $template,
        ], $this->request->resolve([
            'token' => 'foo2',
            'touser' => 'bar2',
            'template' => $template,
            'miniprogram_state' => 'developer',
            'page' => '/pages/index/index',
            'lang' => 'en_US',
        ]));
    }

    public function testBuild(): void
    {
        $template = new Template('baz');
        $requestOptions = $this->request->build(['token' => 'foo', 'touser' => 'bar', 'template' => $template]);

        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(SubscribeMessage::URL, $requestOptions->getUrl());
        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'touser' => 'bar',
                'template_id' => 'baz',
                'miniprogram_state' => 'formal',
                'lang' => 'zh_CN',
            ],
        ], $requestOptions->toArray());

        $template->addData('key1', 'key1_value');
        $template->addData('key2', 'key2_value');

        $requestOptions = $this->request->build([
            'token' => 'foo',
            'touser' => 'bar',
            'template' => $template,
            'miniprogram_state' => 'developer',
            'page' => '/pages/index/index',
            'lang' => 'en_US',
        ]);

        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
            ],
            'json' => [
                'touser' => 'bar',
                'template_id' => 'baz',
                'miniprogram_state' => 'developer',
                'page' => '/pages/index/index',
                'lang' => 'en_US',
                'data' => [
                    'key1' => [
                        'value' => 'key1_value',
                    ],
                    'key2' => [
                        'value' => 'key2_value',
                    ],
                ],
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = [
            'msgid' => 1024,
        ];

        $body = json_encode($data, \JSON_THROW_ON_ERROR);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $template = new Template('baz');
        $result = (new SubscribeMessage($client))->send(['token' => 'foo', 'touser' => 'bar', 'template' => $template]);
        static::assertSame($data, $result);
    }

    public function testSendWithParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(16);
        $this->expectExceptionMessage('test error');

        $data = [
            'msgid' => 1024,
        ];

        $data = [
            'errcode' => 16,
            'errmsg' => 'test error',
        ];

        $body = json_encode($data, \JSON_THROW_ON_ERROR);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $template = new Template('baz');
        (new SubscribeMessage($client))->send(['token' => 'foo', 'touser' => 'bar', 'template' => $template]);
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
        $this->expectExceptionMessage(\sprintf('The option "template" with value "baz" is expected to be of type "%s", but is of type "string"', Template::class));

        $this->request->build(['token' => 'foo', 'touser' => 'bar', 'template' => 'baz']);
    }
}
