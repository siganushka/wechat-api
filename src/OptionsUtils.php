<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionConfigurator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OptionsUtils
{
    public const SIGN_TYPE_SHA256 = 'HMAC-SHA256';
    public const SIGN_TYPE_MD5 = 'MD5';

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

    public static function mchid(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('mchid')
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function mchkey(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('mchkey')
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function mch_client_cert(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('mch_client_cert')
            ->required()
            ->allowedTypes('string')
            ->normalize(function (Options $options, ?string $mchClientCert) {
                if (null !== $mchClientCert && !is_file($mchClientCert)) {
                    throw new InvalidOptionsException('The option "mch_client_cert" file does not exists.');
                }

                return $mchClientCert;
            })
        ;
    }

    public static function mch_client_key(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('mch_client_key')
            ->required()
            ->allowedTypes('string')
            ->normalize(function (Options $options, ?string $mchClientKey) {
                if (null !== $mchClientKey && !is_file($mchClientKey)) {
                    throw new InvalidOptionsException('The option "mch_client_key" file does not exists.');
                }

                return $mchClientKey;
            })
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

    public static function sign_type(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('sign_type')
            ->default(static::SIGN_TYPE_MD5)
            ->allowedValues(static::SIGN_TYPE_MD5, static::SIGN_TYPE_SHA256)
        ;
    }

    public static function nonce_str(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('nonce_str')
            ->default(GenericUtils::getNonceStr())
            ->allowedTypes('string')
        ;
    }

    public static function timestamp(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('timestamp')
            ->default(GenericUtils::getTimestamp())
            ->allowedTypes('string')
        ;
    }

    public static function client_ip(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('client_ip')
            ->default(GenericUtils::getClientIp())
            ->allowedTypes('string')
        ;
    }

    public static function using_slave_url(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('using_slave_url')
            ->default(false)
            ->allowedTypes('bool')
        ;
    }
}
