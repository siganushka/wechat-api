<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core\Request;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Core\Request\AccessTokenRequest;

class AccessTokenRequestTest extends TestCase
{
    public function testAll(): void
    {
        $request = static::createRequest();
        static::assertNull($request->getMethod());
        static::assertNull($request->getUrl());
        static::assertSame([], $request->getOptions());

        $request->build();
        static::assertSame('GET', $request->getMethod());
        static::assertSame(AccessTokenRequest::URL, $request->getUrl());

        /**
         * @var array{
         *  query: array{ appid: string, secret: string, grant_type: string }
         * }
         */
        $options = $request->getOptions();
        static::assertSame('test_appid', $options['query']['appid']);
        static::assertSame('test_appsecret', $options['query']['secret']);
        static::assertSame('client_credential', $options['query']['grant_type']);
    }

    public function testParseResponse(): void
    {
        $data = [
            'access_token' => 'foo',
            'expires_in' => 600,
        ];

        /** @var string */
        $body = json_encode($data);
        $response = ResponseFactory::createMockResponse($body);

        $request = static::createRequest();
        static::assertSame(7200, $request->getCacheTtl());
        static::assertSame($data, $request->parseResponse($response));
        static::assertSame($data['expires_in'], $request->getCacheTtl());
    }

    public function testParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(12);
        $this->expectExceptionMessage('bar');

        $data = [
            'errcode' => 12,
            'errmsg' => 'bar',
        ];

        /** @var string */
        $body = json_encode($data);
        $response = ResponseFactory::createMockResponse($body);

        $request = static::createRequest();
        $request->parseResponse($response);
    }

    public static function createRequest(): AccessTokenRequest
    {
        $options = [
            'appid' => 'test_appid',
            'appsecret' => 'test_appsecret',
        ];

        $configuration = new Configuration($options);
        $request = new AccessTokenRequest($configuration);

        return $request;
    }
}
