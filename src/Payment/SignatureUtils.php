<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;

/**
 * Wechat payment signature utils class.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=4_3
 */
class SignatureUtils
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function generate(array $parameters): string
    {
        if (null === $this->configuration['mchkey']) {
            throw new NoConfigurationException('No configured value for "mchkey" option.');
        }

        ksort($parameters);
        $parameters['key'] = $this->configuration['mchkey'];

        $signature = http_build_query($parameters);
        $signature = urldecode($signature);

        $signature = ('HMAC-SHA256' === $this->configuration['sign_type'])
            ? hash_hmac('sha256', $signature, $this->configuration['mchkey'])
            : hash('md5', $signature);

        return strtoupper($signature);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function check(array $parameters, string $sign): bool
    {
        return 0 === strcmp($sign, $this->generate($parameters));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function checkParameters(array $parameters, string $signName = 'sign'): bool
    {
        if (!isset($parameters[$signName])) {
            return false;
        }

        /** @var string */
        $sign = $parameters[$signName];
        unset($parameters[$signName]);

        return $this->check($parameters, $sign);
    }
}
