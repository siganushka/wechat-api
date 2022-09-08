<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\OptionsConfigurableInterface;
use Siganushka\ApiClient\OptionsConfigurableTrait;
use Siganushka\ApiClient\Wechat\OptionsUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat payment parameter utils class.
 */
class ParameterUtils implements OptionsConfigurableInterface
{
    use OptionsConfigurableTrait;

    final public function __construct()
    {
    }

    /**
     * @return static
     */
    public static function create(): self
    {
        return new static();
    }

    /**
     * 生成 JSAPI 支付参数.
     *
     * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_4.shtml
     *
     * @param string $prepayId 统一下单接口返回的 prepay_id 参数
     *
     * @return array JSAPI 支付参数
     */
    public function jsapi(array $options = []): array
    {
        $resolved = $this->resolve($options);
        $parameter = [
            'appId' => $resolved['appid'],
            'timeStamp' => $resolved['timestamp'],
            'nonceStr' => $resolved['noncestr'],
            'package' => sprintf('prepay_id=%s', $options['prepay_id']),
            'signType' => $resolved['sign_type'],
        ];

        // Generate signature
        $parameter['paySign'] = SignatureUtils::create()->generate([
            'mchkey' => $resolved['mchkey'],
            'sign_type' => $resolved['sign_type'],
            'data' => $parameter,
        ]);

        return $parameter;
    }

    /**
     * 生成 APP 支付参数.
     *
     * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_2_4.shtml
     *
     * @param string $prepayId 统一下单接口返回的 prepay_id 参数
     *
     * @return array APP 支付参数
     */
    public function app(array $options = []): array
    {
        $resolved = $this->resolve($options);
        $parameter = [
            'appid' => $resolved['appid'],
            'partnerid' => $resolved['mchid'],
            'prepayid' => $options['prepay_id'],
            'package' => 'Sign=WXPay',
            'noncestr' => $resolved['noncestr'],
            'timestamp' => $resolved['timestamp'],
        ];

        // Generate signature
        $parameter['sign'] = SignatureUtils::create()->generate([
            'mchkey' => $resolved['mchkey'],
            'sign_type' => $resolved['sign_type'],
            'data' => $parameter,
        ]);

        return $parameter;
    }

    protected function resolve(array $options = []): array
    {
        $resolver = new OptionsResolver();
        $this->configure($resolver);

        return $resolver->resolve($options);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::appid($resolver);
        OptionsUtils::mchid($resolver);
        OptionsUtils::mchkey($resolver);
        OptionsUtils::sign_type($resolver);
        OptionsUtils::timestamp($resolver);
        OptionsUtils::noncestr($resolver);

        $resolver
            ->define('prepay_id')
            ->required()
            ->allowedTypes('string')
        ;
    }
}
