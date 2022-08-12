<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerIpTest extends TestCase
{
    private ?ServerIp $request = null;

    protected function setUp(): void
    {
        $this->request = new ServerIp();
    }

    protected function tearDown(): void
    {
        $this->request = null;
    }

    public function testDefinedOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertSame([
            'token',
        ], $resolver->getDefinedOptions());
    }

    public function testResolve(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertSame([
            'token' => 'foo',
        ], $resolver->resolve(['token' => 'foo']));

        $options = TokenOptionsTest::create();
        $options->configure($resolver);

        static::assertSame([
            'token' => 'test_token',
        ], $resolver->resolve());
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
        $data = [
            'ip_list' => ['foo', 'bar', 'baz'],
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);
        $client = new MockHttpClient($response);

        $result = $this->request->send($client, ['token' => 'foo']);
        static::assertSame($data['ip_list'], $result);
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
}
