<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Jsapi;

use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\GenericUtils;

/**
 * Wechat jsapi config utils class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html
 */
class ConfigUtils
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param array<int, string> $apis
     *
     * @return array{
     *  appId: string,
     *  nonceStr: string,
     *  timestamp: string,
     *  signature: non-empty-string,
     *  jsApiList: array<int, string>,
     *  debug: bool
     * }
     */
    public function generate(string $ticket, array $apis = [], bool $debug = false): array
    {
        $parameters = [
            'jsapi_ticket' => $ticket,
            'timestamp' => GenericUtils::getTimestamp(),
            'noncestr' => GenericUtils::getNonceStr(),
            'url' => GenericUtils::getCurrentUrl(),
        ];

        ksort($parameters);
        $signature = http_build_query($parameters);
        $signature = urldecode($signature);
        $signature = sha1($signature);

        /** @var string */
        $appid = $this->configuration['appid'];

        $config = [
            'appId' => $appid,
            'nonceStr' => $parameters['noncestr'],
            'timestamp' => $parameters['timestamp'],
            'signature' => $signature,
            'jsApiList' => $apis,
            'debug' => $debug,
        ];

        return $config;
    }
}
