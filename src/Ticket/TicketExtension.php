<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Ticket;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\Resolver\ConfigurableOptionsTrait;
use Siganushka\ApiClient\Resolver\OptionsExtensionInterface;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Core\AccessTokenExtension;
use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TicketExtension implements OptionsExtensionInterface
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
        $accessTokenExtension = new AccessTokenExtension($this->configuration);

        $request = new Ticket($this->cachePool);
        $request->setHttpClient($this->httpClient);
        $request->extend($accessTokenExtension);

        $result = $request->send();
        $resolver->setDefault('ticket', $result['ticket']);
    }

    public static function getExtendedClasses(): iterable
    {
        return [
            ConfigUtils::class,
        ];
    }
}
