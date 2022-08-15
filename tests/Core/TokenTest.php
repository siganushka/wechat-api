<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Core\Token;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationOptionsTest;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TokenTest extends TestCase
{
    private ?Token $request = null;

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

        static::assertContains('appid', $resolver->getDefinedOptions());
        static::assertContains('secret', $resolver->getDefinedOptions());
    }

    public function testResolve(): void
    {
        $resolved = $this->request->resolve(['appid' => 'foo', 'secret' => 'bar']);
        static::assertArrayNotHasKey('using_config', $resolved);
        static::assertSame('foo', $resolved['appid']);
        static::assertSame('bar', $resolved['secret']);

        $this->request->using(ConfigurationOptionsTest::create());

        $resolved = $this->request->resolve();
        static::assertSame('default', $resolved['using_config']);
        static::assertSame('test_appid', $resolved['appid']);
        static::assertSame('test_secret', $resolved['secret']);

        $resolved = $this->request->resolve(['using_config' => 'custom']);
        static::assertSame('custom', $resolved['using_config']);
        static::assertSame('custom_appid', $resolved['appid']);
        static::assertSame('custom_secret', $resolved['secret']);
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

        $this->request->using(ConfigurationOptionsTest::create());

        $requestOptions = $this->request->build();
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(Token::URL, $requestOptions->getUrl());
        static::assertEquals([
            'query' => [
                'appid' => 'test_appid',
                'secret' => 'test_secret',
                'grant_type' => 'client_credential',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = [
            'access_token' => 'foo',
            'expires_in' => 1024,
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);
        $client = new MockHttpClient($response);

        $result = $this->request->send($client, ['appid' => 'foo', 'secret' => 'bar']);
        static::assertSame($data, $result);
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

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        $this->request->resolve(['secret' => 'bar']);
    }

    public function testSecretMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "secret" is missing');

        $this->request->resolve(['appid' => 'foo']);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->resolve(['appid' => 123, 'secret' => 'bar']);
    }

    public function testSecretInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "secret" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->resolve(['appid' => 'foo', 'secret' => 123]);
    }
}
