<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\OptionsResolvableInterface;
use Siganushka\ApiClient\OptionsResolvableTrait;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\WechatOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat payment config utils class.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=7_7&index=6
 */
class ConfigUtils implements OptionsResolvableInterface
{
    use OptionsResolvableTrait;

    public function generate(string $prepayId): array
    {
        $resolved = $this->resolve();

        $parameters = [
            'appId' => $resolved['appid'],
            'signType' => $resolved['sign_type'],
            'timeStamp' => $resolved['timestamp'],
            'nonceStr' => $resolved['nonce_str'],
            'package' => sprintf('prepay_id=%s', $prepayId),
        ];

        $signatureUtils = SignatureUtils::create();
        if (isset($this->configurators[ConfigurationOptions::class])) {
            $signatureUtils->using($this->configurators[ConfigurationOptions::class]);
        }

        // Generate pay signature
        $parameters['paySign'] = $signatureUtils->generate($parameters);

        return $parameters;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        WechatOptions::appid($resolver);
        WechatOptions::sign_type($resolver);
        WechatOptions::timestamp($resolver);
        WechatOptions::nonce_str($resolver);
    }
}
