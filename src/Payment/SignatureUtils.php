<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\OptionsConfiguratorInterface;
use Siganushka\ApiClient\OptionsConfiguratorTrait;
use Siganushka\ApiClient\Wechat\OptionsUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat payment signature utils class.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=4_3
 */
class SignatureUtils implements OptionsConfiguratorInterface
{
    use OptionsConfiguratorTrait;

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
     * @param array $data 要发送的数据集合
     *
     * @return string JSAPI 支付参数
     */
    public function generate(array $data): string
    {
        return $this->generateFromOptions(['data' => $data]);
    }

    /**
     * @param array $options 自定义 JSAPI 支付参数
     */
    public function generateFromOptions(array $options = []): string
    {
        $resolver = new OptionsResolver();
        $this->configure($resolver);

        $resolved = $resolver->resolve($options);
        // data to signature
        $data = $resolved['data'];

        ksort($data);
        $data['key'] = $resolved['mchkey'];

        $signature = http_build_query($data);
        $signature = urldecode($signature);

        $signature = (OptionsUtils::SIGN_TYPE_SHA256 === $resolved['sign_type'])
            ? hash_hmac('sha256', $signature, $resolved['mchkey'])
            : hash('md5', $signature);

        return strtoupper($signature);
    }

    public function check(string $sign, array $data): bool
    {
        return 0 === strcmp($sign, $this->generate($data));
    }

    public function checkFromOptions(string $sign, array $options = []): bool
    {
        return 0 === strcmp($sign, $this->generateFromOptions($options));
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::mchkey($resolver);
        OptionsUtils::sign_type($resolver);

        $resolver
            ->define('data')
            ->required()
            ->allowedTypes('array')
        ;
    }
}
