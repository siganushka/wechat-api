<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Test\RequestTestCase;
use Siganushka\ApiClient\Wechat\OAuth\CheckToken;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckTokenTest extends RequestTestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertSame([
            'access_token',
            'openid',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'access_token' => 'foo',
            'openid' => 'bar',
        ], $resolver->resolve(['access_token' => 'foo', 'openid' => 'bar']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['access_token' => 'foo', 'openid' => 'bar']);

        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(CheckToken::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
                'openid' => 'bar',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = [
            'errcode' => 0,
            'errmsg' => 'ok',
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);
        $client = new MockHttpClient($response);

        $result = $this->request->send($client, ['access_token' => 'foo', 'openid' => 'bar']);
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

    public function testAccessTokenMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "access_token" is missing');

        $this->request->build(['openid' => 'bar']);
    }

    public function testAccessTokenInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "access_token" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['access_token' => 123, 'openid' => 'bar']);
    }

    public function testOpenidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "openid" is missing');

        $this->request->build(['access_token' => 'foo']);
    }

    public function testOpenidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "openid" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['access_token' => 'foo', 'openid' => 123]);
    }

    protected function createRequest(): CheckToken
    {
        return new CheckToken();
    }
}
