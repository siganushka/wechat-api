<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Siganushka\ApiClient\AbstractConfiguration;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Configuration extends AbstractConfiguration
{
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
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('mchkey')
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('mch_client_cert')
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
            ->default('MD5')
            ->allowedTypes('string')
            ->allowedValues('MD5', 'HMAC-SHA256')
        ;
    }
}
