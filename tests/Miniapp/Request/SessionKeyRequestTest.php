<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core\Request;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Miniapp\Request\SessionKeyRequest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class SessionKeyRequestTest extends TestCase
{
    public function testAll(): void
    {
        $request = static::createRequest();
        static::assertNull($request->getMethod());
        static::assertNull($request->getUrl());
        static::assertSame([], $request->getOptions());

        $request->build(['js_code' => '123']);
        static::assertSame('GET', $request->getMethod());
        static::assertSame(SessionKeyRequest::URL, $request->getUrl());

        /**
         * @var array{
         *  query: array{ appid: string, secret: string, grant_type: string, js_code: string }
         * }
         */
        $options = $request->getOptions();
        static::assertSame('test_appid', $options['query']['appid']);
        static::assertSame('test_appsecret', $options['query']['secret']);
        static::assertSame('authorization_code', $options['query']['grant_type']);
        static::assertSame('123', $options['query']['js_code']);
    }

    public function testJsCodeMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "js_code" is missing');

        $request = static::createRequest();
        $request->build();
    }

    public function testJsCodeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "js_code" with value 123 is expected to be of type "string", but is of type "int"');

        $request = static::createRequest();
        $request->build(['js_code' => 123]);
    }

    public function testParseResponse(): void
    {
        $data = [
            'openid' => 'test_openid',
            'session_key' => 'test_session_key',
        ];

        /** @var string */
        $body = json_encode($data);
        $response = ResponseFactory::createMockResponse($body);

        $request = static::createRequest();
        static::assertSame($data, $request->parseResponse($response));
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

    public static function createRequest(): SessionKeyRequest
    {
        $options = [
            'appid' => 'test_appid',
            'appsecret' => 'test_appsecret',
        ];

        $configuration = new Configuration($options);
        $request = new SessionKeyRequest($configuration);

        return $request;
    }
}
