<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\OAuth;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class AccessTokenTest extends TestCase
{
    public function testAll(): void
    {
        $request = static::createRequest();
        static::assertNull($request->getMethod());
        static::assertNull($request->getUrl());
        static::assertEquals([], $request->getOptions());

        $options = [
            'code' => 'foo',
        ];

        $request->build($options);
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(AccessToken::URL, $request->getUrl());

        /**
         * @var array{
         *  query: array{ appid: string, secret: string, code: string, grant_type: string }
         * }
         */
        $options = $request->getOptions();
        static::assertSame('test_appid', $options['query']['appid']);
        static::assertSame('test_secret', $options['query']['secret']);
        static::assertSame('foo', $options['query']['code']);
        static::assertSame('authorization_code', $options['query']['grant_type']);

        $request->build([
            'code' => 'bar',
            'using_open_api' => true,
        ]);

        /**
         * @var array{
         *  query: array{ appid: string, secret: string }
         * }
         */
        $options = $request->getOptions();
        static::assertSame('test_open_appid', $options['query']['appid']);
        static::assertSame('test_open_secret', $options['query']['secret']);
    }

    public function testUsingOpenApiOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "using_open_api" with value 1 is expected to be of type "bool", but is of type "int"');

        $request = static::createRequest();
        $request->build([
            'code' => 'bar',
            'using_open_api' => 1,
        ]);
    }

    public static function createRequest(): AccessToken
    {
        $configuration = ConfigurationTest::createConfiguration();

        return new AccessToken($configuration);
    }
}
