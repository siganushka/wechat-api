<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class OptionSet
{
    public static function appid(OptionsResolver $resolver): void
    {
        $resolver
            ->define(__FUNCTION__)
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function secret(OptionsResolver $resolver): void
    {
        $resolver
            ->define(__FUNCTION__)
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function token(OptionsResolver $resolver): void
    {
        $resolver
            ->define(__FUNCTION__)
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function ticket(OptionsResolver $resolver): void
    {
        $resolver
            ->define(__FUNCTION__)
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function timestamp(OptionsResolver $resolver): void
    {
        $resolver
            ->define(__FUNCTION__)
            ->default((string) time())
            ->allowedTypes('string')
        ;
    }

    public static function noncestr(OptionsResolver $resolver): void
    {
        $resolver
            ->define(__FUNCTION__)
            ->default(bin2hex(random_bytes(16)))
            ->allowedTypes('string')
        ;
    }
}
