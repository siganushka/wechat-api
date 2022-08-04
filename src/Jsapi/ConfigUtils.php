<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Jsapi;

use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\GenericUtils;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;

/**
 * Wechat jsapi config utils class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html
 */
class ConfigUtils
{
    private Configuration $configuration;
    private Ticket $ticket;

    public function __construct(Configuration $configuration, Ticket $ticket)
    {
        $this->configuration = $configuration;
        $this->ticket = $ticket;
    }

    public function generate(array $apis = [], bool $debug = false): array
    {
        $result = $this->ticket->send();

        $parameters = [
            'jsapi_ticket' => $result['ticket'],
            'timestamp' => GenericUtils::getTimestamp(),
            'noncestr' => GenericUtils::getNonceStr(),
            'url' => GenericUtils::getCurrentUrl(),
        ];

        ksort($parameters);
        $signature = http_build_query($parameters);
        $signature = urldecode($signature);
        $signature = sha1($signature);

        $config = [
            'appId' => $this->configuration['appid'],
            'nonceStr' => $parameters['noncestr'],
            'timestamp' => $parameters['timestamp'],
            'signature' => $signature,
            'jsApiList' => $apis,
            'debug' => $debug,
        ];

        return $config;
    }
}
