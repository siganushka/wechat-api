<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\RequestRegistry;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Core\CallbackIp;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Siganushka\ApiClient\Wechat\Extension\AccessTokenExtension;
use Siganushka\ApiClient\Wechat\Message\Template\Message;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AccessTokenExtensionTest extends TestCase
{
    public function testConfigureOptions(): void
    {
        $data = [
            'access_token' => 'test_access_token_extension',
        ];

        $response = ResponseFactory::createMockResponseWithJson($data);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $cachePool = new FilesystemAdapter();
        $cachePool->clear();

        $configuration = ConfigurationTest::createConfiguration();

        $registry = new RequestRegistry($httpClient, [
            new AccessToken($cachePool, $configuration),
        ]);

        $resolver = new OptionsResolver();
        static::assertSame([], $resolver->resolve());

        $extension = new AccessTokenExtension($registry);
        $extension->configureOptions($resolver);

        static::assertSame(['access_token' => 'test_access_token_extension'], $resolver->resolve());
    }

    public function testExtendedRequests(): void
    {
        static::assertSame([
            Ticket::class,
            ServerIp::class,
            CallbackIp::class,
            Message::class,
        ], AccessTokenExtension::getExtendedRequests());
    }
}
