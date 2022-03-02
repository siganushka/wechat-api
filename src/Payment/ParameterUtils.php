<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\Wechat\Configuration;

class ParameterUtils
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsapi(string $prepayId): array
    {
        $parameters = [
            'appId' => $this->configuration['appid'],
            'signType' => $this->configuration['sign_type'],
            'timeStamp' => (string) time(),
            'nonceStr' => bin2hex(random_bytes(16)),
            'package' => sprintf('prepay_id=%s', $prepayId),
        ];

        $signatureUtils = new SignatureUtils($this->configuration);
        $parameters['sign'] = $signatureUtils->generate($parameters);

        return $parameters;
    }
}
