<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerIpTest extends TestCase
{
    protected ?ServerIp $request = null;

    protected function setUp(): void
    {
        $this->request = new ServerIp();
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
            'token',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'token' => 'foo',
        ], $resolver->resolve(['token' => 'foo']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['token' => 'foo']);
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(ServerIp::URL, $requestOptions->getUrl());
        static::assertSame([
            'query' => [
                'access_token' => 'foo',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = ['ip_list' => ['foo', 'bar', 'baz']];
        $body = json_encode($data);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $result = (new ServerIp($client))->send(['token' => 'foo']);
        static::assertSame($data['ip_list'], $result);
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

        (new ServerIp($client))->send(['token' => 'foo']);
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
