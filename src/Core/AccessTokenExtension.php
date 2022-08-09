<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Core;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\Resolver\ConfigurableOptionsTrait;
use Siganushka\ApiClient\Resolver\OptionsExtensionInterface;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\ConfigurationExtension;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Siganushka\ApiClient\Wechat\Miniapp\WxacodeUnlimited;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AccessTokenExtension implements OptionsExtensionInterface
{
    use ConfigurableOptionsTrait;

    protected Configuration $configuration;
    protected HttpClientInterface $httpClient;
    protected CacheItemPoolInterface $cachePool;

    public function __construct(Configuration $configuration, HttpClientInterface $httpClient = null, CacheItemPoolInterface $cachePool = null)
    {
        $this->configuration = $configuration;
        $this->httpClient = $httpClient ?? HttpClient::create();
        $this->cachePool = $cachePool ?? new FilesystemAdapter();
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $configurationException = new ConfigurationExtension($this->configuration);

        $request = new AccessToken($this->cachePool);
        $request->setHttpClient($this->httpClient);
        $request->extend($configurationException);

        $result = $request->send();
        $resolver->setDefault('access_token', $result['access_token']);
    }

    public static function getExtendedClasses(): iterable
    {
        return [
            CallbackIp::class,
            ServerIp::class,
            Qrcode::class,
            Wxacode::class,
            WxacodeUnlimited::class,
            Message::class,
            Ticket::class,
        ];
    }
}
