<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Siganushka\ApiClient\Resolver\ConfigurableOptionsTrait;
use Siganushka\ApiClient\Resolver\OptionsExtensionInterface;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;
use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;
use Siganushka\ApiClient\Wechat\Payment\Query;
use Siganushka\ApiClient\Wechat\Payment\Refund;
use Siganushka\ApiClient\Wechat\Payment\Transfer;
use Siganushka\ApiClient\Wechat\Payment\Unifiedorder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationExtension implements OptionsExtensionInterface
{
    use ConfigurableOptionsTrait;

    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults($this->configuration->toArray());
    }

    public static function getExtendedClasses(): iterable
    {
        return [
            AccessToken::class,
            SessionKey::class,
            RefreshToken::class,
            Query::class,
            Refund::class,
            Transfer::class,
            Unifiedorder::class,
        ];
    }
}
