<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Core;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiFactory\ResolverExtensionInterface;
use Siganushka\ApiFactory\Wechat\Configuration;
use Siganushka\ApiFactory\Wechat\Jsapi\ConfigUtils;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TicketExtension implements ResolverExtensionInterface
{
    protected Configuration $configuration;
    protected ?HttpClientInterface $httpClient = null;
    protected ?CacheItemPoolInterface $cachePool = null;

    public function __construct(Configuration $configuration, ?HttpClientInterface $httpClient = null, ?CacheItemPoolInterface $cachePool = null)
    {
        $this->configuration = $configuration;
        $this->httpClient = $httpClient;
        $this->cachePool = $cachePool;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->define('type')
            ->default('jsapi')
            ->allowedValues('jsapi', 'wx_card')
        ;

        $tokenExtension = new TokenExtension($this->configuration, $this->httpClient, $this->cachePool);
        $tokenExtension->configureOptions($resolver);

        $resolver->setDefault('ticket', function (Options $options): string {
            $request = new Ticket($this->httpClient, $this->cachePool);

            $result = $request->send([
                'token' => $options['token'],
                'type' => $options['type'],
            ]);

            return $result['ticket'];
        });
    }

    public static function getExtendedClasses(): iterable
    {
        return [
            ConfigUtils::class,
        ];
    }
}
