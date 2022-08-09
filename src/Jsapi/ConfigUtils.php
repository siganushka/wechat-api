<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Jsapi;

use Siganushka\ApiClient\Resolver\ExtendableOptionsInterface;
use Siganushka\ApiClient\Resolver\ExtendableOptionsTrait;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\GenericUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat jsapi config utils class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html
 */
class ConfigUtils implements ExtendableOptionsInterface
{
    use ExtendableOptionsTrait;

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
            'noncestr' => $resolved['noncestr'],
            'url' => $resolved['url'],
        ];

        ksort($parameters);
        $signature = http_build_query($parameters);
        $signature = urldecode($signature);
        $signature = sha1($signature);

        $config = [
            'appId' => $resolved['appid'],
            'timestamp' => $resolved['timestamp'],
            'nonceStr' => $resolved['noncestr'],
            'signature' => $signature,
            'jsApiList' => $resolved['apis'],
            'debug' => $resolved['debug'],
        ];

        return $config;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        Configuration::apply($resolver);

        $resolver
            ->define('ticket')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('timestamp')
            ->default(GenericUtils::getTimestamp())
            ->allowedTypes('string')
        ;

        $resolver
            ->define('noncestr')
            ->default(GenericUtils::getNonceStr())
            ->allowedTypes('string')
        ;

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
