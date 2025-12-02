<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Jsapi;

use Siganushka\ApiFactory\ResolverInterface;
use Siganushka\ApiFactory\ResolverTrait;
use Siganushka\ApiFactory\Wechat\OptionSet;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html
 */
class ConfigUtils implements ResolverInterface
{
    use ResolverTrait;

    /**
     * 生成 JSSDK 配置参数.
     *
     * @param array $options 自定义配置参数
     *
     * @return array JSSDK 配置参数
     */
    public function generate(array $options = []): array
    {
        $resolved = $this->resolve($options);
        $data = [
            'jsapi_ticket' => $resolved['ticket'],
            'timestamp' => $resolved['timestamp'],
            'noncestr' => $resolved['noncestr'],
            'url' => $resolved['url'],
        ];

        ksort($data);
        $signature = http_build_query($data);
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
        OptionSet::appid($resolver);
        OptionSet::ticket($resolver);
        OptionSet::timestamp($resolver);
        OptionSet::noncestr($resolver);

        /** @var array{ HTTPS?: string, HTTP_HOST?: string, REQUEST_URI?: string } */
        $server = $_SERVER;
        $currentUrl = (isset($server['HTTPS']) ? 'https://' : 'http://').
            ($server['HTTP_HOST'] ?? 'localhost').
            ($server['REQUEST_URI'] ?? '');

        $resolver
            ->define('url')
            ->default($currentUrl)
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
