<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Jsapi;

use Siganushka\ApiClient\ConfigurableSubjectInterface;
use Siganushka\ApiClient\ConfigurableSubjectTrait;
use Siganushka\ApiClient\Wechat\GenericUtils;
use Siganushka\ApiClient\Wechat\OptionsUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat jsapi config utils class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html
 */
class ConfigUtils implements ConfigurableSubjectInterface
{
    use ConfigurableSubjectTrait;

    /**
     * @param array $apis  需要使用的 JS 接口列表
     * @param bool  $debug 是否开启调试模式
     *
     * @return array JSSDK 配置参数
     */
    public function generate(array $apis = [], bool $debug = false): array
    {
        return $this->generateFromOptions(['apis' => $apis, 'debug' => $debug]);
    }

    /**
     * @param array{
     *  appid?: string,
     *  ticket?: string,
     *  timestamp?: string,
     *  nonce_str?: string,
     *  url?: string,
     *  apis?: array,
     *  debug?: bool
     * } $options 自定义 JSSDK 配置选项
     *
     * @return array{
     *  appId: string,
     *  timestamp: string,
     *  nonceStr: string,
     *  signature: string,
     *  jsApiList: array,
     *  debug: bool
     * } JSSDK 配置参数
     */
    public function generateFromOptions(array $options = []): array
    {
        $resolver = new OptionsResolver();
        $this->configure($resolver);

        $resolved = $resolver->resolve($options);
        $data = [
            'jsapi_ticket' => $resolved['ticket'],
            'timestamp' => $resolved['timestamp'],
            'noncestr' => $resolved['nonce_str'],
            'url' => $resolved['url'],
        ];

        ksort($data);
        $signature = http_build_query($data);
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
        OptionsUtils::appid($resolver);
        OptionsUtils::ticket($resolver);
        OptionsUtils::timestamp($resolver);
        OptionsUtils::nonce_str($resolver);

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
