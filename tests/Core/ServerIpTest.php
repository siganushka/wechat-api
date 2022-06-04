<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ServerIpTest extends TestCase
{
    public function testResolve(): void
    {
        $serverIp = static::createRequest();

        $resolved = $serverIp->resolve(['access_token' => 'foo']);
        static::assertSame(['access_token' => 'foo'], $resolved);
        static::assertSame(['access_token'], $serverIp->getResolver()->getDefinedOptions());
    }

    public function testSend(): void
    {
        $data = [
            'ip_list' => ['foo', 'bar', 'baz'],
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $serverIp = static::createRequest();
        $serverIp->setHttpClient($httpClient);

        $parsedResponse = $serverIp->send(['access_token' => 'foo']);
        static::assertSame($data['ip_list'], $parsedResponse);
    }

    public function testConfigureRequest(): void
    {
        $serverIp = static::createRequest();
        $request = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($serverIp, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($serverIp, $request, $serverIp->resolve(['access_token' => 'foo']));

        static::assertSame('GET', $request->getMethod());
        static::assertSame(ServerIp::URL, $request->getUrl());
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

        $serverIp = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($serverIp, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($serverIp, $response);
    }

    public function testAccessTokenMissingException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "access_token" is missing');

        $serverIp = static::createRequest();
        $serverIp->resolve();
    }

    public function testAccessTokenInvalidException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "access_token" with value 123 is expected to be of type "string", but is of type "int"');

        $serverIp = static::createRequest();
        $serverIp->resolve(['access_token' => 123]);
    }

    public static function createRequest(): ServerIp
    {
        return new ServerIp();
    }
}
