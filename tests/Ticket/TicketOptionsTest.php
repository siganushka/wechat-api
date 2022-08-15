<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Ticket;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\ConfigurationManager;
use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationManagerTest;
use Siganushka\ApiClient\Wechat\Ticket\TicketOptions;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketOptionsTest extends TestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();

        $ticketOptions = static::create();
        $ticketOptions->configure($resolver);

        static::assertContains('using_config', $resolver->getDefinedOptions());
        static::assertContains('appid', $resolver->getDefinedOptions());
        static::assertContains('secret', $resolver->getDefinedOptions());
        static::assertContains('token', $resolver->getDefinedOptions());
        static::assertContains('ticket', $resolver->getDefinedOptions());
    }

    public function testResolve(): void
    {
        $resolver = new OptionsResolver();

        $ticketOptions = static::create();
        $ticketOptions->configure($resolver);

        $resolved = $resolver->resolve();
        static::assertSame('default', $resolved['using_config']);
        static::assertSame('test_appid', $resolved['appid']);
        static::assertSame('test_secret', $resolved['secret']);
        static::assertSame('test_token', $resolved['token']);
        static::assertSame('test_ticket', $resolved['ticket']);
        static::assertSame('jsapi', $resolved['type']);

        $resolved = $resolver->resolve(['using_config' => 'custom']);
        static::assertSame('custom', $resolved['using_config']);
        static::assertSame('custom_appid', $resolved['appid']);
        static::assertSame('custom_secret', $resolved['secret']);
        static::assertSame('custom_token', $resolved['token']);
        static::assertSame('custom_ticket', $resolved['ticket']);
        static::assertSame('jsapi', $resolved['type']);

        $resolved = $resolver->resolve(['using_config' => 'custom', 'token' => 'foo', 'type' => 'wx_card']);
        static::assertSame('custom', $resolved['using_config']);
        static::assertSame('custom_appid', $resolved['appid']);
        static::assertSame('custom_secret', $resolved['secret']);
        static::assertSame('foo', $resolved['token']);
        static::assertSame('custom_ticket_2', $resolved['ticket']);
        static::assertSame('wx_card', $resolved['type']);
    }

    public function testGetExtendedRequests(): void
    {
        $ticketOptions = static::create();

        $extendedRequests = $ticketOptions::getExtendedRequests();
        static::assertCount(1, $extendedRequests);
        static::assertContains(ConfigUtils::class, $extendedRequests);
    }

    public function testUsingConfigInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "using_config" with value "foo" is invalid. Accepted values are: "default", "custom"');

        $resolver = new OptionsResolver();

        $ticketOptions = static::create();
        $ticketOptions->configure($resolver);

        $resolver->resolve(['using_config' => 'foo']);
    }

    public static function create(ConfigurationManager $configurationManager = null): TicketOptions
    {
        if (null === $configurationManager) {
            $configurationManager = ConfigurationManagerTest::create();
        }

        $client = new MockHttpClient([
            ResponseFactory::createMockResponseWithJson([
                'access_token' => 'test_token',
                'expires_in' => 1024,
            ]),
            ResponseFactory::createMockResponseWithJson([
                'ticket' => 'test_ticket',
            ]),
            ResponseFactory::createMockResponseWithJson([
                'access_token' => 'custom_token',
                'expires_in' => 1024,
            ]),
            ResponseFactory::createMockResponseWithJson([
                'ticket' => 'custom_ticket',
            ]),
            ResponseFactory::createMockResponseWithJson([
                'ticket' => 'custom_ticket_2',
            ]),
        ]);

        $cache = new NullAdapter();

        return new TicketOptions($configurationManager, $client, $cache);
    }
}
