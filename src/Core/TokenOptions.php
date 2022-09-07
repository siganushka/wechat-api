<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Core;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\OptionsExtensionInterface;
use Siganushka\ApiClient\OptionsExtensionTrait;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Siganushka\ApiClient\Wechat\Miniapp\WxacodeUnlimited;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TokenOptions implements OptionsExtensionInterface
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
        $configurationOptions = new ConfigurationOptions($this->configuration);
        $configurationOptions->configure($resolver);

        $resolver->setDefault('token', function (Options $options): string {
            $request = new Token($this->httpClient, $this->cachePool);

            $result = $request->send([
                'appid' => $options['appid'],
                'secret' => $options['secret'],
            ]);

            return $result['access_token'];
        });
    }

    public static function getExtendedClasses(): array
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
