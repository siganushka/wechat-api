<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\Affiaccount;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\Wechat\Affiaccount\UserInfo;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class UserInfoTest extends TestCase
{
    protected UserInfo $request;

    protected function setUp(): void
    {
        $this->request = new UserInfo();
    }

    public function testResolve(): void
    {
        static::assertEquals([
            'token' => 'foo',
            'openid' => 'test_openid',
            'lang' => 'zh_CN',
        ], $this->request->resolve(['openid' => 'test_openid', 'token' => 'foo']));
    }

    public function testBuild(): void
    {
        $requestOptions = $this->request->build(['openid' => 'test_openid', 'token' => 'foo']);
        static::assertSame('GET', $requestOptions->getMethod());
        static::assertSame('https://api.weixin.qq.com/cgi-bin/user/info', $requestOptions->getUrl());
        static::assertEquals([
            'query' => [
                'access_token' => 'foo',
                'openid' => 'test_openid',
                'lang' => 'zh_CN',
            ],
        ], $requestOptions->toArray());
    }

    public function testSend(): void
    {
        $data = ['foo', 'bar', 'baz'];
        $body = json_encode($data, \JSON_THROW_ON_ERROR);

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $result = (new UserInfo($client))->send(['openid' => 'test_openid', 'token' => 'foo']);
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

        (new UserInfo($client))->send(['openid' => 'test_openid', 'token' => 'foo']);
    }

    public function testOpenidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "openid" is missing');

        $this->request->build(['token' => 'test_token']);
    }

    public function testTokenMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "token" is missing');

        $this->request->build(['openid' => 'test_openid']);
    }

    public function testTokenInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "token" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build(['openid' => 'test', 'token' => 123]);
    }
}
