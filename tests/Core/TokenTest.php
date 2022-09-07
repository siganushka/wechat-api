<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wechat\Core\Token;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TokenTest extends TestCase
{
    protected ?Token $request = null;

    protected function setUp(): void
    {
        $this->request = new Token();
    }

    protected function tearDown(): void
    {
        $this->request = null;
    }

    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertSame([
            'appid',
            'secret',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'appid' => 'foo',
            'secret' => 'bar',
        ], $resolver->resolve(['appid' => 'foo', 'secret' => 'bar']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['appid' => 'foo', 'secret' => 'bar']);
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(Token::URL, $requestOptions->getUrl());
        static::assertSame([
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
        $body = json_encode($data);

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
        $body = json_encode($data);

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
