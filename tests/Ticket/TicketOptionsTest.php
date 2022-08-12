<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Ticket;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Siganushka\ApiClient\Wechat\Ticket\TicketOptions;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketOptionsTest extends TestCase
{
    public function testDefinedOptions(): void
    {
        $resolver = new OptionsResolver();

        $options = static::create();
        $options->configure($resolver);

        static::assertSame([
            'ticket',
        ], $resolver->getDefinedOptions());
    }

    public function testResolve(): void
    {
        $resolver = new OptionsResolver();

        $options = static::create();
        $options->configure($resolver);

        static::assertSame([
            'ticket' => 'test_ticket',
        ], $resolver->resolve());
    }

    public function testCustomOptions(): void
    {
        $resolver = new OptionsResolver();

        $options = static::create();
        $options->configure($resolver);

        static::assertSame([
            'ticket' => 'bar',
        ], $resolver->resolve(['ticket' => 'bar']));
    }

    public static function create(Configuration $configuration = null): TicketOptions
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::create();
        }

        $accessTokenData = [
            'access_token' => 'test_token',
            'expires_in' => 1024,
        ];

        $ticketData = [
            'ticket' => 'test_ticket',
        ];

        $client = new MockHttpClient([
            ResponseFactory::createMockResponseWithJson($accessTokenData),
            ResponseFactory::createMockResponseWithJson($ticketData),
        ]);

        return new TicketOptions($configuration, $client);
    }
}
