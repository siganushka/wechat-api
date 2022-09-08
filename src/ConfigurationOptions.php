<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Siganushka\ApiClient\OptionsExtensionInterface;
use Siganushka\ApiClient\OptionsExtensionTrait;
use Siganushka\ApiClient\Wechat\Core\Token;
use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken;
use Siganushka\ApiClient\Wechat\OAuth\Client;
use Siganushka\ApiClient\Wechat\OAuth\Qrcode;
use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;
use Siganushka\ApiClient\Wechat\Payment\ParameterUtils;
use Siganushka\ApiClient\Wechat\Payment\Query;
use Siganushka\ApiClient\Wechat\Payment\Refund;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;
use Siganushka\ApiClient\Wechat\Payment\Transfer;
use Siganushka\ApiClient\Wechat\Payment\Unifiedorder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationOptions implements OptionsExtensionInterface
{
    use OptionsExtensionTrait;

    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        foreach ($this->configuration as $key => $value) {
            if (null !== $value) {
                $resolver->setDefault($key, $value);
            }
        }
    }

    public static function getExtendedClasses(): array
    {
        return [
            Token::class,
            SessionKey::class,
            Client::class,
            Qrcode::class,
            AccessToken::class,
            RefreshToken::class,
            Query::class,
            Refund::class,
            Transfer::class,
            ParameterUtils::class,
            SignatureUtils::class,
            Unifiedorder::class,
        ];
    }
}
