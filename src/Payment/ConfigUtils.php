<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\OptionsExtendableInterface;
use Siganushka\ApiClient\OptionsExtendableTrait;
use Siganushka\ApiClient\Wechat\Utils\GenericUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat payment config utils class.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=7_7&index=6
 */
class ConfigUtils implements OptionsExtendableInterface
{
    use OptionsExtendableTrait;

    private SignatureUtils $signatureUtils;

    public function __construct(SignatureUtils $signatureUtils = null)
    {
        $this->signatureUtils = $signatureUtils ?? new SignatureUtils();
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['appid', 'sign_type']);

        $resolver->setDefaults([
            'timestamp' => GenericUtils::getTimestamp(),
            'noncestr' => GenericUtils::getNonceStr(),
            'sign_type' => 'HMAC-SHA256',
        ]);

        $resolver->setAllowedTypes('appid', 'string');
        $resolver->setAllowedTypes('timestamp', 'string');
        $resolver->setAllowedTypes('noncestr', 'string');
        $resolver->setAllowedValues('sign_type', ['MD5', 'HMAC-SHA256']);
    }

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

        // Extending resolvable from current class
        foreach ($this->resolvables as $resolvable) {
            $this->signatureUtils->extend($resolvable);
        }

        // Generate pay signature
        $parameters['paySign'] = $this->signatureUtils->generate($parameters);

        return $parameters;
    }
}
