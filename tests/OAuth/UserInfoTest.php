<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\Wechat\OAuth\UserInfo;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class UserInfoTest extends TestCase
{
    protected ?UserInfo $request = null;

    protected function setUp(): void
    {
        $this->request = new UserInfo();
    }

    protected function tearDown(): void
    {
        $this->request = null;
    }

    public function testResolve(): void
    {
        static::assertEquals([
            'lang' => 'zh_CN',
            'access_token' => 'foo',
            'openid' => 'bar',
        ], $this->request->resolve(['access_token' => 'foo', 'openid' => 'bar']));

        static::assertEquals([
            'lang' => 'en',
            'access_token' => 'foo',
            'openid' => 'bar',
        ], $this->request->resolve(['access_token' => 'foo', 'openid' => 'bar', 'lang' => 'en']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['access_token' => 'foo', 'openid' => 'bar']);

        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame(UserInfo::URL, $requestOptions->getUrl());
        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
                'openid' => 'bar',
                'lang' => 'zh_CN',
            ],
        ], $requestOptions->toArray());

        $requestOptions = $this->request->build(['access_token' => 'foo', 'openid' => 'bar', 'lang' => 'en']);

        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
                'openid' => 'bar',
                'lang' => 'en',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = [
            'openid' => 'test_openid',
            'nickname' => 'test_nickname',
        ];

        $body = json_encode($data);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $result = (new UserInfo($client))->send(['access_token' => 'foo', 'openid' => 'bar']);
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

        $body = json_encode($data);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        (new UserInfo($client))->send(['access_token' => 'foo', 'openid' => 'bar']);
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

    public function testLangInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "lang" with value "baz" is invalid. Accepted values are: "zh_CN", "zh_TW", "en"');

        $this->request->build(['access_token' => 'foo', 'openid' => 'bar', 'lang' => 'baz']);
    }
}
