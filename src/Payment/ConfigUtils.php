<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\GenericUtils;

/**
 * Wechat payment config utils class.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=7_7&index=6
 */
class ConfigUtils
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array{
     *  appId: string,
     *  signType: string,
     *  timeStamp: string,
     *  nonceStr: string,
     *  package: string,
     *  paySign: string
     * }
     */
    public function generate(string $prepayId): array
    {
        /** @var string */
        $appid = $this->configuration['appid'];
        /** @var string */
        $signType = $this->configuration['sign_type'];

        $parameters = [
            'appId' => $appid,
            'signType' => $signType,
            'timeStamp' => GenericUtils::getTimestamp(),
            'nonceStr' => GenericUtils::getNonceStr(),
            'package' => sprintf('prepay_id=%s', $prepayId),
        ];

        $signatureUtils = new SignatureUtils($this->configuration);
        $parameters['paySign'] = $signatureUtils->generate($parameters);

        return $parameters;
    }
}
