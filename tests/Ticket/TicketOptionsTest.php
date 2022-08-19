<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Ticket;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Siganushka\ApiClient\Wechat\Ticket\TicketOptions;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketOptionsTest extends TestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();

        $ticketOptions = static::create();
        $ticketOptions->configure($resolver);

        static::assertSame([
            'token',
            'type',
            'appid',
            'secret',
            'mchid',
            'mchkey',
            'mch_client_cert',
            'mch_client_key',
            'ticket',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
            'type' => 'jsapi',
            'token' => 'test_token',
            'ticket' => 'test_ticket',
        ], $resolver->resolve());
    }

    public function testGetExtendedRequests(): void
    {
        $ticketOptions = static::create();

        $extendedRequests = $ticketOptions::getExtendedRequests();
        static::assertCount(1, $extendedRequests);
        static::assertContains(ConfigUtils::class, $extendedRequests);
    }

    public static function create(Configuration $configuration = null): TicketOptions
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::create();
        }

        $client = new MockHttpClient([
            ResponseFactory::createMockResponseWithJson([
                'access_token' => 'test_token',
                'expires_in' => 1024,
            ]),
            ResponseFactory::createMockResponseWithJson([
                'ticket' => 'test_ticket',
            ]),
        ]);

        $cache = new NullAdapter();

        return new TicketOptions($configuration, $client, $cache);
    }
}
