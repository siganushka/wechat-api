<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\OptionsConfigurableInterface;
use Siganushka\ApiClient\OptionsConfigurableTrait;
use Siganushka\ApiClient\Wechat\OptionsUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat payment signature utils class.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=4_3
 */
class SignatureUtils implements OptionsConfigurableInterface
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
     * 生成数据签名.
     *
     * @param array $options 数据签名选项
     *
     * @return string 数据签名
     */
    public function generate(array $options = []): string
    {
        $resolver = new OptionsResolver();
        $this->configure($resolver);

        $resolved = $resolver->resolve($options);
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

    public function check(string $sign, array $options = []): bool
    {
        return 0 === strcmp($sign, $this->generate($options));
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
