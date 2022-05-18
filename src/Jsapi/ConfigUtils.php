<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Jsapi;

use Siganushka\ApiClient\ConfigurableOptionsInterface;
use Siganushka\ApiClient\ConfigurableOptionsTrait;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat jssdk config utils class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html
 */
class ConfigUtils implements ConfigurableOptionsInterface
{
    use ConfigurableOptionsTrait;

    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param array<int|string, mixed> $options
     *
     * @return array{
     *  appId: string,
     *  nonceStr: string,
     *  timestamp: string,
     *  signature: string,
     *  jsApiList: array<int, string>,
     *  debug: bool
     * }
     */
    public function generateConfig(array $options = []): array
    {
        $resolved = $this->resolveOptions($options);
        $parameters = [
            'jsapi_ticket' => $resolved['jsapi_ticket'],
            'noncestr' => $resolved['noncestr'],
            'timestamp' => $resolved['timestamp'],
            'url' => $resolved['url'],
        ];

        ksort($parameters);
        $signature = http_build_query($parameters);
        $signature = urldecode($signature);
        $signature = hash('sha1', $signature);

        return [
            'appId' => $this->configuration['appid'],
            'nonceStr' => $resolved['noncestr'],
            'timestamp' => $resolved['timestamp'],
            'signature' => $signature,
            'jsApiList' => $resolved['jsApiList'],
            'debug' => $resolved['debug'],
        ];
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('jsapi_ticket');
        $resolver->setRequired('url');

        $resolver->setDefault('timestamp', (string) time());
        $resolver->setDefault('noncestr', (string) time());
        $resolver->setDefault('jsApiList', []);
        $resolver->setDefault('debug', false);

        $resolver->setAllowedTypes('jsapi_ticket', 'string');
        $resolver->setAllowedTypes('url', 'string');
        $resolver->setAllowedTypes('timestamp', 'string');
        $resolver->setAllowedTypes('noncestr', 'string');
        $resolver->setAllowedTypes('jsApiList', 'array');
        $resolver->setAllowedTypes('debug', 'boolean');
    }
}
