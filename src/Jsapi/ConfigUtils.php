<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Jsapi;

use Siganushka\ApiClient\OptionsResolvableInterface;
use Siganushka\ApiClient\OptionsResolvableTrait;
use Siganushka\ApiClient\Wechat\GenericUtils;
use Siganushka\ApiClient\Wechat\WechatOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat jsapi config utils class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html
 */
class ConfigUtils implements OptionsResolvableInterface
{
    use OptionsResolvableTrait;

    public function generate(array $apis = [], bool $debug = false): array
    {
        return $this->generateFromOptions(compact('apis', 'debug'));
    }

    public function generateFromOptions(array $options = []): array
    {
        $resolved = $this->resolve($options);
        $parameters = [
            'jsapi_ticket' => $resolved['ticket'],
            'timestamp' => $resolved['timestamp'],
            'noncestr' => $resolved['nonce_str'],
            'url' => $resolved['url'],
        ];

        ksort($parameters);
        $signature = http_build_query($parameters);
        $signature = urldecode($signature);
        $signature = sha1($signature);

        $config = [
            'appId' => $resolved['appid'],
            'timestamp' => $resolved['timestamp'],
            'nonceStr' => $resolved['nonce_str'],
            'signature' => $signature,
            'jsApiList' => $resolved['apis'],
            'debug' => $resolved['debug'],
        ];

        return $config;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        WechatOptions::appid($resolver);
        WechatOptions::ticket($resolver);
        WechatOptions::timestamp($resolver);
        WechatOptions::nonce_str($resolver);

        $resolver
            ->define('url')
            ->default(GenericUtils::getCurrentUrl())
            ->allowedTypes('string')
        ;

        $resolver
            ->define('apis')
            ->default([])
            ->allowedTypes('string[]')
        ;

        $resolver
            ->define('debug')
            ->default(false)
            ->allowedTypes('bool')
        ;
    }
}
