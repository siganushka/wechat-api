<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Siganushka\ApiClient\AbstractConfiguration;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Configuration extends AbstractConfiguration
{
    public const SIGN_TYPE_SHA256 = 'HMAC-SHA256';
    public const SIGN_TYPE_MD5 = 'MD5';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        static::apply($resolver);
    }

    public static function apply(OptionsResolver $resolver): void
    {
        $resolver
            ->define('appid')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('secret')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('mchid')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('mchkey')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('mch_client_cert')
            ->default(null)
            ->allowedTypes('null', 'string')
            ->normalize(function (Options $options, ?string $mchClientCert) {
                if (null !== $mchClientCert && !is_file($mchClientCert)) {
                    throw new InvalidOptionsException('The option "mch_client_cert" file does not exists.');
                }

                return $mchClientCert;
            })
        ;

        $resolver
            ->define('mch_client_key')
            ->default(null)
            ->allowedTypes('null', 'string')
            ->normalize(function (Options $options, ?string $mchClientKey) {
                if (null !== $mchClientKey && !is_file($mchClientKey)) {
                    throw new InvalidOptionsException('The option "mch_client_key" file does not exists.');
                }

                return $mchClientKey;
            })
        ;

        $resolver
            ->define('sign_type')
            ->default(static::SIGN_TYPE_MD5)
            ->allowedValues(static::SIGN_TYPE_MD5, static::SIGN_TYPE_SHA256)
        ;
    }
}
