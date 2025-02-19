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
        $configurationExtension = new ConfigurationExtension($this->configuration);
        $configurationExtension->configureOptions($resolver);

        $resolver->setDefault('token', function (Options $options): string {
            $request = new TokenStable($this->httpClient, $this->cachePool);

            $result = $request->send([
                'appid' => $options['appid'],
                'secret' => $options['secret'],
            ]);

            return $result['access_token'];
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
