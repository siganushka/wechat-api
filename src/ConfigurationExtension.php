<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat;

use Siganushka\ApiFactory\ResolverExtensionInterface;
use Siganushka\ApiFactory\Wechat\Core\Token;
use Siganushka\ApiFactory\Wechat\Core\TokenStable;
use Siganushka\ApiFactory\Wechat\Miniapp\SessionKey;
use Siganushka\ApiFactory\Wechat\OAuth\AccessToken;
use Siganushka\ApiFactory\Wechat\OAuth\Client;
use Siganushka\ApiFactory\Wechat\OAuth\Qrcode;
use Siganushka\ApiFactory\Wechat\OAuth\RefreshToken;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationExtension implements ResolverExtensionInterface
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $fn = fn ($value, string $key) => $resolver->isDefined($key) && null !== $value;

        $configs = $this->configuration->toArray();
        $resolver->setDefaults(array_filter($configs, $fn, \ARRAY_FILTER_USE_BOTH));
    }

    public static function getExtendedClasses(): iterable
    {
        return [
            Token::class,
            TokenStable::class,
            SessionKey::class,
            Client::class,
            Qrcode::class,
            AccessToken::class,
            RefreshToken::class,
        ];
    }
}
