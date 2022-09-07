<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Core;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\OptionsExtensionInterface;
use Siganushka\ApiClient\OptionsExtensionTrait;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TicketOptions implements OptionsExtensionInterface
{
    use OptionsExtensionTrait;

    protected Configuration $configuration;
    protected ?HttpClientInterface $httpClient = null;
    protected ?CacheItemPoolInterface $cachePool = null;

    public function __construct(Configuration $configuration, HttpClientInterface $httpClient = null, CacheItemPoolInterface $cachePool = null)
    {
        $this->configuration = $configuration;
        $this->httpClient = $httpClient;
        $this->cachePool = $cachePool;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $ticket = new Ticket($this->httpClient, $this->cachePool);
        $ticket->configure($resolver);

        $tokenOptions = new TokenOptions($this->configuration, $this->httpClient, $this->cachePool);
        $tokenOptions->configure($resolver);

        $resolver->setDefault('ticket', function (Options $options) use ($ticket): string {
            $result = $ticket->send([
                'token' => $options['token'],
                'type' => $options['type'],
            ]);

            return $result['ticket'];
        });
    }

    public static function getExtendedClasses(): array
    {
        return [
            ConfigUtils::class,
        ];
    }
}
