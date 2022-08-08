<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Core;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\OptionsResolvableTrait;
use Siganushka\ApiClient\RequestOptionsExtensionInterface;
use Siganushka\ApiClient\Wechat\ConfigurationManager;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Siganushka\ApiClient\Wechat\Miniapp\WxacodeUnlimited;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AccessTokenOptions implements RequestOptionsExtensionInterface
{
    use OptionsResolvableTrait;

    private ConfigurationManager $configurationManager;
    private HttpClientInterface $httpClient;
    private CacheItemPoolInterface $cachePool;

    public function __construct(ConfigurationManager $configurationManager, HttpClientInterface $httpClient = null, CacheItemPoolInterface $cachePool = null)
    {
        $this->configurationManager = $configurationManager;
        $this->httpClient = $httpClient ?? HttpClient::create();
        $this->cachePool = $cachePool ?? new FilesystemAdapter();
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $configurationOptions = new ConfigurationOptions($this->configurationManager);

        $request = new AccessToken($this->cachePool);
        $request->setHttpClient($this->httpClient);
        $request->extend($configurationOptions);

        $result = $request->send();
        $resolver->setDefault('access_token', $result['access_token']);
    }

    public static function getExtendedRequests(): iterable
    {
        return [
            Ticket::class,
            CallbackIp::class,
            ServerIp::class,
            Qrcode::class,
            Wxacode::class,
            WxacodeUnlimited::class,
            Message::class,
        ];
    }
}
