<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Ticket;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\RequestOptionsExtensionInterface;
use Siganushka\ApiClient\RequestOptionsExtensionTrait;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Core\TokenOptions;
use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TicketOptions implements RequestOptionsExtensionInterface
{
    use RequestOptionsExtensionTrait;

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
        $request = new Ticket($this->cachePool);
        $request->using(new TokenOptions($this->configuration, $this->httpClient, $this->cachePool));

        $result = $request->send($this->httpClient);
        $resolver->setDefault('ticket', $result['ticket']);
    }

    public static function getExtendedClasses(): array
    {
        return [
            ConfigUtils::class,
        ];
    }
}
