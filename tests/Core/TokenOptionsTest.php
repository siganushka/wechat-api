<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Core;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\ConfigurationManager;
use Siganushka\ApiClient\Wechat\Core\CallbackIp;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Siganushka\ApiClient\Wechat\Core\TokenOptions;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Siganushka\ApiClient\Wechat\Miniapp\WxacodeUnlimited;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationManagerTest;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TokenOptionsTest extends TestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();

        $tokenOptions = static::create();
        $tokenOptions->configure($resolver);

        static::assertSame([
            'using_config',
            'appid',
            'secret',
            'mchid',
            'mchkey',
            'mch_client_cert',
            'mch_client_key',
            'token',
        ], $resolver->getDefinedOptions());
    }

    public function testResolve(): void
    {
        $resolver = new OptionsResolver();

        $configurationManager = ConfigurationManagerTest::create();

        $defaultConfig = $configurationManager->get('default');
        $customConfig = $configurationManager->get('custom');

        $tokenOptions = static::create();
        $tokenOptions->configure($resolver);

        $resolved = $resolver->resolve();
        static::assertSame('default', $resolved['using_config']);
        static::assertSame($defaultConfig['appid'], $resolved['appid']);
        static::assertSame($defaultConfig['secret'], $resolved['secret']);
        static::assertSame($defaultConfig['mchid'], $resolved['mchid']);
        static::assertSame($defaultConfig['mchkey'], $resolved['mchkey']);
        static::assertSame($defaultConfig['mch_client_cert'], $resolved['mch_client_cert']);
        static::assertSame($defaultConfig['mch_client_key'], $resolved['mch_client_key']);
        static::assertSame('test_token_1', $resolved['token']);

        $resolved = $resolver->resolve(['using_config' => 'custom']);
        static::assertSame('custom', $resolved['using_config']);
        static::assertSame($customConfig['appid'], $resolved['appid']);
        static::assertSame($customConfig['secret'], $resolved['secret']);
        static::assertSame($customConfig['mchid'], $resolved['mchid']);
        static::assertSame($customConfig['mchkey'], $resolved['mchkey']);
        static::assertSame($customConfig['mch_client_cert'], $resolved['mch_client_cert']);
        static::assertSame($customConfig['mch_client_key'], $resolved['mch_client_key']);
        static::assertSame('test_token_2', $resolved['token']);

        $resolved = $resolver->resolve(['using_config' => 'custom', 'appid' => 'foo', 'secret' => 'bar']);
        static::assertSame('custom', $resolved['using_config']);
        static::assertSame('foo', $resolved['appid']);
        static::assertSame('bar', $resolved['secret']);
        static::assertSame($customConfig['mchid'], $resolved['mchid']);
        static::assertSame($customConfig['mchkey'], $resolved['mchkey']);
        static::assertSame($customConfig['mch_client_cert'], $resolved['mch_client_cert']);
        static::assertSame($customConfig['mch_client_key'], $resolved['mch_client_key']);
        static::assertSame('test_token_3', $resolved['token']);
    }

    public function testGetExtendedRequests(): void
    {
        $tokenOptions = static::create();

        $extendedRequests = $tokenOptions::getExtendedRequests();
        static::assertCount(7, $extendedRequests);
        static::assertContains(CallbackIp::class, $extendedRequests);
        static::assertContains(ServerIp::class, $extendedRequests);
        static::assertContains(Qrcode::class, $extendedRequests);
        static::assertContains(Wxacode::class, $extendedRequests);
        static::assertContains(WxacodeUnlimited::class, $extendedRequests);
        static::assertContains(Message::class, $extendedRequests);
        static::assertContains(Ticket::class, $extendedRequests);
    }

    public function testUsingConfigInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "using_config" with value "foo" is invalid. Accepted values are: "default", "custom"');

        $resolver = new OptionsResolver();

        $tokenOptions = static::create();
        $tokenOptions->configure($resolver);

        $resolver->resolve(['using_config' => 'foo']);
    }

    public static function create(ConfigurationManager $configurationManager = null): TokenOptions
    {
        if (null === $configurationManager) {
            $configurationManager = ConfigurationManagerTest::create();
        }

        $responses = [];
        foreach (range(1, 3) as $num) {
            $responses[] = ResponseFactory::createMockResponseWithJson([
                'access_token' => sprintf('test_token_%s', $num),
                'expires_in' => 1024,
            ]);
        }

        $client = new MockHttpClient($responses);
        $cache = new NullAdapter();

        return new TokenOptions($configurationManager, $client, $cache);
    }
}
