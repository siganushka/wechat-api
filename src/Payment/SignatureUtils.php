<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\Resolver\ExtendableOptionsInterface;
use Siganushka\ApiClient\Resolver\ExtendableOptionsTrait;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat payment signature utils class.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=4_3
 */
class SignatureUtils implements ExtendableOptionsInterface
{
    use ExtendableOptionsTrait;

    public static function create(): self
    {
        return new static();
    }

    public function generate(array $parameters): string
    {
        $resolved = $this->resolve();
        if (null === $resolved['mchkey']) {
            throw new NoConfigurationException('No configured value for "mchkey" option.');
        }

        ksort($parameters);
        $parameters['key'] = $resolved['mchkey'];

        $signature = http_build_query($parameters);
        $signature = urldecode($signature);

        $signature = (Configuration::SIGN_TYPE_SHA256 === $resolved['sign_type'])
            ? hash_hmac('sha256', $signature, $resolved['mchkey'])
            : hash('md5', $signature);

        return strtoupper($signature);
    }

    public function check(array $parameters, string $sign): bool
    {
        return 0 === strcmp($sign, $this->generate($parameters));
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        Configuration::apply($resolver);
    }
}
