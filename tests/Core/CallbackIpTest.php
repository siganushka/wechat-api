<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Core\CallbackIp;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallbackIpTest extends TestCase
{
    public function testResolve(): void
    {
        $callbackIp = static::createRequest();

        $resolved = $callbackIp->resolve(['access_token' => 'foo']);
        static::assertSame(['access_token' => 'foo'], $resolved);
        static::assertSame(['access_token'], $callbackIp->getResolver()->getDefinedOptions());
    }

    public function testSend(): void
    {
        $data = [
            'ip_list' => ['foo', 'bar', 'baz'],
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $callbackIp = static::createRequest();
        $callbackIp->setHttpClient($httpClient);

        $parsedResponse = $callbackIp->send(['access_token' => 'foo']);
        static::assertSame($data['ip_list'], $parsedResponse);
    }

    public function testConfigureRequest(): void
    {
        $callbackIp = static::createRequest();
        $request = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($callbackIp, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($callbackIp, $request, $callbackIp->resolve(['access_token' => 'foo']));

        static::assertSame('GET', $request->getMethod());
        static::assertSame(CallbackIp::URL, $request->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
        ], $request->toArray());
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

        $callbackIp = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($callbackIp, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($callbackIp, $response);
    }

    public function testAccessTokenMissingException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "access_token" is missing');

        $callbackIp = static::createRequest();
        $callbackIp->resolve();
    }

    public function testAccessTokenInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "access_token" with value 123 is expected to be of type "string", but is of type "int"');

        $callbackIp = static::createRequest();
        $callbackIp->resolve(['access_token' => 123]);
    }

    public static function createRequest(): CallbackIp
    {
        return new CallbackIp();
    }
}
