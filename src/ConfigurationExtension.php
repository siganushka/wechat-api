<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat;

use Siganushka\ApiFactory\ResolverExtensionInterface;
use Siganushka\ApiFactory\Wechat\Core\Token;
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
        foreach ($this->configuration as $key => $value) {
            if (null !== $value) {
                $resolver->setDefault($key, $value);
            }
        }
    }

    public static function getExtendedClasses(): iterable
    {
        return [
            Token::class,
            SessionKey::class,
            Client::class,
            Qrcode::class,
            AccessToken::class,
            RefreshToken::class,
        ];
    }
}
