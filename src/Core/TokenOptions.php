<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Core;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\RequestOptionsExtensionInterface;
use Siganushka\ApiClient\RequestOptionsExtensionTrait;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Siganushka\ApiClient\Wechat\Miniapp\WxacodeUnlimited;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TokenOptions implements RequestOptionsExtensionInterface
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
        $configurationOptions = new ConfigurationOptions($this->configuration);
        $configurationOptions->configure($resolver);

        $resolver->setDefault('token', function (Options $options) {
            $request = new Token($this->cachePool);
            $result = $request->send($this->httpClient, [
                'appid' => $options['appid'],
                'secret' => $options['secret'],
            ]);

            return $result['access_token'];
        });
    }

    public static function getExtendedRequests(): array
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
