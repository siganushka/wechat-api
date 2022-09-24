<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Symfony\Component\OptionsResolver\OptionConfigurator;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OptionsUtils
{
    public static function appid(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('appid')
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function secret(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('secret')
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function token(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('token')
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function ticket(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('ticket')
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function timestamp(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('timestamp')
            ->default((string) time())
            ->allowedTypes('string')
        ;
    }

    public static function noncestr(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('noncestr')
            ->default(bin2hex(random_bytes(16)))
            ->allowedTypes('string')
        ;
    }
}
