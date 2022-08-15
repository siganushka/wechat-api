<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
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

    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertContains('token', $resolver->getDefinedOptions());
    }

    public function testResolve(): void
    {
        $resolved = $this->request->resolve(['token' => 'foo']);
        static::assertArrayNotHasKey('using_config', $resolved);
        static::assertSame('foo', $resolved['token']);

        $this->request->using(TokenOptionsTest::create());

        $resolved = $this->request->resolve();
        static::assertSame('default', $resolved['using_config']);
        static::assertSame('test_token_1', $resolved['token']);

        $resolved = $this->request->resolve(['using_config' => 'custom']);
        static::assertSame('custom', $resolved['using_config']);
        static::assertSame('test_token_2', $resolved['token']);
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['token' => 'foo']);
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(ServerIp::URL, $requestOptions->getUrl());
        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
            ],
        ], $requestOptions->toArray());

        $this->request->using(TokenOptionsTest::create());

        $requestOptions = $this->request->build();
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(ServerIp::URL, $requestOptions->getUrl());
        static::assertEquals([
            'query' => [
                'access_token' => 'test_token_1',
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

    public function testSendWithParseResponseException(): void
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

    public function testTokenMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "token" is missing');

        $this->request->resolve();
    }

    public function testTokenInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "token" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->resolve(['token' => 123]);
    }
}
