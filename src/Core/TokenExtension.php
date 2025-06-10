<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Core;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiFactory\ResolverExtensionInterface;
use Siganushka\ApiFactory\Wechat\Configuration;
use Siganushka\ApiFactory\Wechat\ConfigurationExtension;
use Siganushka\ApiFactory\Wechat\Message\SubscribeMessage;
use Siganushka\ApiFactory\Wechat\Message\TemplateMessage;
use Siganushka\ApiFactory\Wechat\Miniapp\Qrcode;
use Siganushka\ApiFactory\Wechat\Miniapp\Wxacode;
use Siganushka\ApiFactory\Wechat\Miniapp\WxacodeUnlimited;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TokenExtension implements ResolverExtensionInterface
{
    protected readonly Configuration $configuration;
    protected readonly ?HttpClientInterface $httpClient;
    protected readonly ?CacheItemPoolInterface $cachePool;

    public function __construct(Configuration $configuration, ?HttpClientInterface $httpClient = null, ?CacheItemPoolInterface $cachePool = null)
    {
        $this->configuration = $configuration;
        $this->httpClient = $httpClient;
        $this->cachePool = $cachePool;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('token', function (Options $options): string {
            $request = new TokenStable($this->httpClient, $this->cachePool);
            $request->extend(new ConfigurationExtension($this->configuration));

            return $request->send()['access_token'];
        });
    }

    public static function getExtendedClasses(): iterable
    {
        return [
            CallbackIp::class,
            ServerIp::class,
            Qrcode::class,
            Wxacode::class,
            WxacodeUnlimited::class,
            SubscribeMessage::class,
            TemplateMessage::class,
            Ticket::class,
        ];
    }
}
