<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\ConfigurableSubjectInterface;
use Siganushka\ApiClient\ConfigurableSubjectTrait;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\WechatOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat payment config utils class.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=7_7&index=6
 */
class ConfigUtils implements ConfigurableSubjectInterface
{
    use ConfigurableSubjectTrait;

    public function generate(string $prepayId): array
    {
        return $this->generateFromOptions(['prepay_id' => $prepayId]);
    }

    public function generateFromOptions(array $options = []): array
    {
        $resolver = new OptionsResolver();
        $this->configure($resolver);

        $resolved = $resolver->resolve($options);
        $parameters = [
            'appId' => $resolved['appid'],
            'signType' => $resolved['sign_type'],
            'timeStamp' => $resolved['timestamp'],
            'nonceStr' => $resolved['nonce_str'],
            'package' => sprintf('prepay_id=%s', $resolved['prepay_id']),
        ];

        $signatureUtils = SignatureUtils::create();
        if (isset($this->configurators[ConfigurationOptions::class])) {
            $signatureUtils->using($this->configurators[ConfigurationOptions::class]);
        }

        // Generate pay signature
        $parameters['paySign'] = $signatureUtils->generateFromOptions([
            'mchkey' => $resolved['mchkey'],
            'sign_type' => $resolved['sign_type'],
            'parameters' => $parameters,
        ]);

        return $parameters;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        WechatOptions::appid($resolver);
        WechatOptions::mchkey($resolver);
        WechatOptions::sign_type($resolver);
        WechatOptions::timestamp($resolver);
        WechatOptions::nonce_str($resolver);

        $resolver
            ->define('prepay_id')
            ->required()
            ->allowedTypes('string')
        ;
    }
}
