<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\Wechat\Core\CallbackIp;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CallbackIpTest extends TestCase
{
    protected CallbackIp $request;

    protected function setUp(): void
    {
        $this->request = new CallbackIp();
    }

    public function testResolve(): void
    {
        static::assertEquals([
            'token' => 'foo',
        ], $this->request->resolve(['token' => 'foo']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['token' => 'foo']);
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame('https://api.weixin.qq.com/cgi-bin/getcallbackip', $requestOptions->getUrl());
        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = ['ip_list' => ['foo', 'bar', 'baz']];
        $body = json_encode($data, \JSON_THROW_ON_ERROR);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $result = (new CallbackIp($client))->send(['token' => 'foo']);
        static::assertSame($data['ip_list'], $result);
    }

    public function testSendWithParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(16);
        $this->expectExceptionMessage('test error');

        $data = ['errcode' => 16, 'errmsg' => 'test error'];
        $body = json_encode($data, \JSON_THROW_ON_ERROR);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        (new CallbackIp($client))->send(['token' => 'foo']);
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
}
