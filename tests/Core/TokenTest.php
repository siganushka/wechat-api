<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\Wechat\Core\Token;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class TokenTest extends TestCase
{
    protected Token $request;

    protected function setUp(): void
    {
        $this->request = new Token();
    }

    public function testResolve(): void
    {
        static::assertEquals([
            'appid' => 'foo',
            'secret' => 'bar',
        ], $this->request->resolve(['appid' => 'foo', 'secret' => 'bar']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['appid' => 'foo', 'secret' => 'bar']);
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(Token::URL, $requestOptions->getUrl());
        static::assertEquals([
            'query' => [
                'appid' => 'foo',
                'secret' => 'bar',
                'grant_type' => 'client_credential',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = ['access_token' => 'foo', 'expires_in' => 1024];
        $body = json_encode($data, \JSON_THROW_ON_ERROR);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $result = (new Token($client))->send(['appid' => 'foo', 'secret' => 'bar']);
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

        (new Token($client, $cachePool))->send(['appid' => 'foo', 'secret' => 'bar']);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        $this->request->build(['secret' => 'bar']);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['appid' => 123, 'secret' => 'bar']);
    }

    public function testSecretMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "secret" is missing');

        $this->request->build(['appid' => 'foo']);
    }

    public function testSecretInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "secret" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['appid' => 'foo', 'secret' => 123]);
    }
}
