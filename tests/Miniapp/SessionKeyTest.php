<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\Miniapp;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\Wechat\Miniapp\SessionKey;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class SessionKeyTest extends TestCase
{
    protected SessionKey $request;

    protected function setUp(): void
    {
        $this->request = new SessionKey();
    }

    public function testResolve(): void
    {
        static::assertEquals([
            'appid' => 'foo',
            'secret' => 'bar',
            'code' => 'baz',
        ], $this->request->resolve(['appid' => 'foo', 'secret' => 'bar', 'code' => 'baz']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['appid' => 'foo', 'secret' => 'bar', 'code' => 'baz']);
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame('https://api.weixin.qq.com/sns/jscode2session', $requestOptions->getUrl());
        static::assertEquals([
            'query' => [
                'appid' => 'foo',
                'secret' => 'bar',
                'grant_type' => 'authorization_code',
                'js_code' => 'baz',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = ['openid' => 'foo', 'session_key' => 'bar'];
        $body = json_encode($data, \JSON_THROW_ON_ERROR);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $result = (new SessionKey($client))->send(['appid' => 'foo', 'secret' => 'bar', 'code' => 'baz']);
        static::assertSame($data, $result);
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

        $cachePool = new NullAdapter();

        (new SessionKey($client, $cachePool))->send(['appid' => 'foo', 'secret' => 'bar', 'code' => 'baz']);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        $this->request->build(['secret' => 'bar', 'code' => 'baz']);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['appid' => 123, 'secret' => 'bar', 'code' => 'baz']);
    }

    public function testSecretMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "secret" is missing');

        $this->request->build(['appid' => 'foo', 'code' => 'baz']);
    }

    public function testSecretInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "secret" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['appid' => 'foo', 'secret' => 123, 'code' => 'baz']);
    }

    public function testCodeMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "code" is missing');

        $this->request->build(['appid' => 'foo', 'secret' => 'bar']);
    }

    public function testCodeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "code" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['appid' => 'foo', 'secret' => 'bar', 'code' => 123]);
    }
}
