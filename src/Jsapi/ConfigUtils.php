<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Jsapi;

use Siganushka\ApiClient\OptionsExtendableInterface;
use Siganushka\ApiClient\OptionsExtendableTrait;
use Siganushka\ApiClient\Wechat\Utils\GenericUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat jsapi config utils class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html
 */
class ConfigUtils implements OptionsExtendableInterface
{
    use OptionsExtendableTrait;

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['appid', 'ticket']);

        $resolver->setDefaults([
            'timestamp' => GenericUtils::getTimestamp(),
            'noncestr' => GenericUtils::getNonceStr(),
            'url' => GenericUtils::getCurrentUrl(),
            'apis' => [],
            'debug' => false,
        ]);

        $resolver->setAllowedTypes('appid', 'string');
        $resolver->setAllowedTypes('ticket', 'string');
        $resolver->setAllowedTypes('timestamp', 'string');
        $resolver->setAllowedTypes('noncestr', 'string');
        $resolver->setAllowedTypes('url', 'string');
        $resolver->setAllowedTypes('apis', 'string[]');
        $resolver->setAllowedTypes('debug', 'bool');
    }

    public function generate(array $options = []): array
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
            'nonceStr' => $resolved['noncestr'],
            'timestamp' => $resolved['timestamp'],
            'signature' => $signature,
            'jsApiList' => $resolved['apis'],
            'debug' => $resolved['debug'],
        ];

        return $config;
    }
}
