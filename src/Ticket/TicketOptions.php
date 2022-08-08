<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Ticket;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\OptionsResolvableTrait;
use Siganushka\ApiClient\RequestOptionsExtensionInterface;
use Siganushka\ApiClient\Wechat\ConfigurationManager;
use Siganushka\ApiClient\Wechat\Core\AccessTokenOptions;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TicketOptions implements RequestOptionsExtensionInterface
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
        $accessTokenOptions = new AccessTokenOptions($this->configurationManager);

        $request = new Ticket($this->cachePool);
        $request->setHttpClient($this->httpClient);
        $request->extend($accessTokenOptions);

        $result = $request->send();
        $resolver->setDefault('ticket', $result['ticket']);
    }

    public static function getExtendedRequests(): iterable
    {
        return [];
    }
}
