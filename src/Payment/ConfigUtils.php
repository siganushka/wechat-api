<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\ConfigurableSubjectInterface;
use Siganushka\ApiClient\ConfigurableSubjectTrait;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\OptionsUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat payment config utils class.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=7_7&index=6
 */
class ConfigUtils implements ConfigurableSubjectInterface
{
    use ConfigurableSubjectTrait;

    /**
     * @param string $prepayId 统一下单接口返回的prepay_id参数
     *
     * @return array JSAPI 配置参数
     */
    public function generate(string $prepayId): array
    {
        return $this->generateFromOptions(['prepay_id' => $prepayId]);
    }

    /**
     * @param array $options 自定义 JSAPI 配置参数
     *
     * @return array JSAPI 配置参数
     */
    public function generateFromOptions(array $options = []): array
    {
        $resolver = new OptionsResolver();
        $this->configure($resolver);

        $resolved = $resolver->resolve($options);
        $data = [
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
        $data['paySign'] = $signatureUtils->generateFromOptions([
            'mchkey' => $resolved['mchkey'],
            'sign_type' => $resolved['sign_type'],
            'data' => $data,
        ]);

        return $data;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::appid($resolver);
        OptionsUtils::mchkey($resolver);
        OptionsUtils::sign_type($resolver);
        OptionsUtils::timestamp($resolver);
        OptionsUtils::nonce_str($resolver);

        $resolver
            ->define('prepay_id')
            ->required()
            ->allowedTypes('string')
        ;
    }
}
