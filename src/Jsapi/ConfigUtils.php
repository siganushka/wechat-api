<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Jsapi;

use Siganushka\ApiClient\OptionsConfigurableInterface;
use Siganushka\ApiClient\OptionsConfigurableTrait;
use Siganushka\ApiClient\Wechat\GenericUtils;
use Siganushka\ApiClient\Wechat\OptionsUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat jsapi config utils class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html
 */
class ConfigUtils implements OptionsConfigurableInterface
{
    use OptionsConfigurableTrait;

    final public function __construct()
    {
    }

    /**
     * @return static
     */
    public static function create(): self
    {
        return new static();
    }

    /**
     * 生成 JSSDK 配置参数.
     *
     * @param array $options 自定义配置参数
     *
     * @return array JSSDK 配置参数
     */
    public function generate(array $options = []): array
    {
        $resolver = new OptionsResolver();
        $this->configure($resolver);

        $resolved = $resolver->resolve($options);
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
        OptionsUtils::appid($resolver);
        OptionsUtils::ticket($resolver);
        OptionsUtils::timestamp($resolver);
        OptionsUtils::noncestr($resolver);

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
