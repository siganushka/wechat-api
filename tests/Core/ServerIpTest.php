<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class ServerIpTest extends TestCase
{
    public function testAll(): void
    {
        $request = static::createRequest();
        static::assertNull($request->getMethod());
        static::assertNull($request->getUrl());
        static::assertSame([], $request->getOptions());

        $request->build(['access_token' => '123']);
        static::assertSame('GET', $request->getMethod());
        static::assertSame(ServerIp::URL, $request->getUrl());

        /**
         * @var array{
         *  query: array{ access_token: string }
         * }
         */
        $options = $request->getOptions();
        static::assertSame('123', $options['query']['access_token']);
    }

    public function testAccessTokenMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "access_token" is missing');

        $request = static::createRequest();
        $request->build();
    }

    public function testAccessTokenInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "access_token" with value 123 is expected to be of type "string", but is of type "int"');

        $request = static::createRequest();
        $request->build(['access_token' => 123]);
    }

    public function testParseResponse(): void
    {
        $data = [
            'ip_list' => ['foo', 'bar', 'baz'],
        ];

        /** @var string */
        $body = json_encode($data);
        $response = ResponseFactory::createMockResponse($body);

        $request = static::createRequest();
        static::assertSame($data['ip_list'], $request->parseResponse($response));
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

    public static function createRequest(): ServerIp
    {
        $request = new ServerIp();

        return $request;
    }
}
