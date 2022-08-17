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

        static::assertSame([
            'token',
            'type',
            'using_config',
            'appid',
            'secret',
            'mchid',
            'mchkey',
            'mch_client_cert',
            'mch_client_key',
            'ticket',
        ], $resolver->getDefinedOptions());

        $configurationManager = ConfigurationManagerTest::create();

        $defaultConfig = $configurationManager->get('default');
        $customConfig = $configurationManager->get('custom');

        static::assertSame([
            'type' => 'jsapi',
            'using_config' => 'default',
            'appid' => $defaultConfig['appid'],
            'secret' => $defaultConfig['secret'],
            'mchid' => $defaultConfig['mchid'],
            'mchkey' => $defaultConfig['mchkey'],
            'mch_client_cert' => $defaultConfig['mch_client_cert'],
            'mch_client_key' => $defaultConfig['mch_client_key'],
            'token' => 'test_token',
            'ticket' => 'test_ticket',
        ], $resolver->resolve());

        static::assertSame([
            'type' => 'jsapi',
            'using_config' => 'custom',
            'appid' => $customConfig['appid'],
            'secret' => $customConfig['secret'],
            'mchid' => $customConfig['mchid'],
            'mchkey' => $customConfig['mchkey'],
            'mch_client_cert' => $customConfig['mch_client_cert'],
            'mch_client_key' => $customConfig['mch_client_key'],
            'token' => 'custom_token',
            'ticket' => 'custom_ticket',
        ], $resolver->resolve(['using_config' => 'custom']));
    }

    public function testResolve(): void
    {
        $resolver = new OptionsResolver();

        $configurationManager = ConfigurationManagerTest::create();

        $defaultConfig = $configurationManager->get('default');
        $customConfig = $configurationManager->get('custom');

        $ticketOptions = static::create($configurationManager);
        $ticketOptions->configure($resolver);

        $resolved = $resolver->resolve();
        static::assertSame('test_token', $resolved['token']);
        static::assertSame('jsapi', $resolved['type']);
        static::assertSame('default', $resolved['using_config']);
        static::assertSame($defaultConfig['appid'], $resolved['appid']);
        static::assertSame($defaultConfig['secret'], $resolved['secret']);
        static::assertSame($defaultConfig['mchid'], $resolved['mchid']);
        static::assertSame($defaultConfig['mchkey'], $resolved['mchkey']);
        static::assertSame($defaultConfig['mch_client_cert'], $resolved['mch_client_cert']);
        static::assertSame($defaultConfig['mch_client_key'], $resolved['mch_client_key']);
        static::assertSame('test_ticket', $resolved['ticket']);

        $resolved = $resolver->resolve(['using_config' => 'custom']);
        static::assertSame('custom_token', $resolved['token']);
        static::assertSame('jsapi', $resolved['type']);
        static::assertSame('custom', $resolved['using_config']);
        static::assertSame($customConfig['appid'], $resolved['appid']);
        static::assertSame($customConfig['secret'], $resolved['secret']);
        static::assertSame($customConfig['mchid'], $resolved['mchid']);
        static::assertSame($customConfig['mchkey'], $resolved['mchkey']);
        static::assertSame($customConfig['mch_client_cert'], $resolved['mch_client_cert']);
        static::assertSame($customConfig['mch_client_key'], $resolved['mch_client_key']);
        static::assertSame('custom_ticket', $resolved['ticket']);

        $resolved = $resolver->resolve(['using_config' => 'custom', 'token' => 'foo', 'type' => 'wx_card']);
        static::assertSame('foo', $resolved['token']);
        static::assertSame('wx_card', $resolved['type']);
        static::assertSame('custom', $resolved['using_config']);
        static::assertSame($customConfig['appid'], $resolved['appid']);
        static::assertSame($customConfig['secret'], $resolved['secret']);
        static::assertSame($customConfig['mchid'], $resolved['mchid']);
        static::assertSame($customConfig['mchkey'], $resolved['mchkey']);
        static::assertSame($customConfig['mch_client_cert'], $resolved['mch_client_cert']);
        static::assertSame($customConfig['mch_client_key'], $resolved['mch_client_key']);
        static::assertSame('custom_ticket_2', $resolved['ticket']);
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
