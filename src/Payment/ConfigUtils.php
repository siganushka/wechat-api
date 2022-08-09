<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\Resolver\ExtendableOptionsInterface;
use Siganushka\ApiClient\Resolver\ExtendableOptionsTrait;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\ConfigurationExtension;
use Siganushka\ApiClient\Wechat\GenericUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat payment config utils class.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=7_7&index=6
 */
class ConfigUtils implements ExtendableOptionsInterface
{
    use ExtendableOptionsTrait;

    public function generate(string $prepayId): array
    {
        $resolved = $this->resolve();

        $parameters = [
            'appId' => $resolved['appid'],
            'signType' => $resolved['sign_type'],
            'timeStamp' => GenericUtils::getTimestamp(),
            'nonceStr' => GenericUtils::getNonceStr(),
            'package' => sprintf('prepay_id=%s', $prepayId),
        ];

        $signatureUtils = SignatureUtils::create();
        if (isset($this->extensions[ConfigurationExtension::class])) {
            $signatureUtils->extend($this->extensions[ConfigurationExtension::class]);
        }

        // Generate pay signature
        $parameters['paySign'] = $signatureUtils->generate($parameters);

        return $parameters;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        Configuration::apply($resolver);
    }
}
