<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Core\TokenOptions;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TokenOptionsTest extends TestCase
{
    public function testDefinedOptions(): void
    {
        $resolver = new OptionsResolver();

        $options = static::create();
        $options->configure($resolver);

        static::assertSame([
            'token',
        ], $resolver->getDefinedOptions());
    }

    public function testResolve(): void
    {
        $resolver = new OptionsResolver();

        $options = static::create();
        $options->configure($resolver);

        static::assertSame([
            'token' => 'test_token',
        ], $resolver->resolve());
    }

    public function testCustomOptions(): void
    {
        $resolver = new OptionsResolver();

        $options = static::create();
        $options->configure($resolver);

        static::assertSame([
            'token' => 'bar',
        ], $resolver->resolve(['token' => 'bar']));
    }

    public static function create(Configuration $configuration = null): TokenOptions
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::create();
        }

        $data = [
            'access_token' => 'test_token',
            'expires_in' => 1024,
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);
        $client = new MockHttpClient($response);

        return new TokenOptions($configuration, $client);
    }
}
