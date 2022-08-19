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
use Symfony\Component\OptionsResolver\Options;
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
        $ticket = new Ticket($this->cachePool);
        $ticket->configure($resolver);

        $tokenOptions = new TokenOptions($this->configuration, $this->httpClient, $this->cachePool);
        $tokenOptions->configure($resolver);

        $resolver->setDefault('ticket', function (Options $options) use ($ticket) {
            $result = $ticket->send($this->httpClient, [
                'token' => $options['token'],
                'type' => $options['type'],
            ]);

            return $result['ticket'];
        });
    }

    public static function getExtendedRequests(): array
    {
        return [
            ConfigUtils::class,
        ];
    }
}
