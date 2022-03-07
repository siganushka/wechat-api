<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Siganushka\ApiClient\AbstractConfiguration;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat configuration.
 */
class Configuration extends AbstractConfiguration
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('appid');
        $resolver->setRequired('appsecret');

        $resolver->setDefaults([
            'mchid' => null,
            'mchkey' => null,
            'client_cert_file' => null,
            'client_key_file' => null,
            'sign_type' => 'MD5',
        ]);

        $resolver->setAllowedTypes('appid', 'string');
        $resolver->setAllowedTypes('appsecret', 'string');
        $resolver->setAllowedTypes('mchid', ['null', 'string']);
        $resolver->setAllowedTypes('mchkey', ['null', 'string']);
        $resolver->setAllowedTypes('client_cert_file', ['null', 'string']);
        $resolver->setAllowedTypes('client_key_file', ['null', 'string']);
        $resolver->setAllowedValues('sign_type', ['MD5', 'HMAC-SHA256']);

        $resolver->setNormalizer('client_cert_file', function (Options $options, ?string $clientCertFile) {
            if ($clientCertFile && !is_file($clientCertFile)) {
                throw new InvalidOptionsException('The option "client_cert_file" file does not exists.');
            }

            return $clientCertFile;
        });

        $resolver->setNormalizer('client_key_file', function (Options $options, ?string $clientKeyFile) {
            if ($clientKeyFile && !is_file($clientKeyFile)) {
                throw new InvalidOptionsException('The option "client_key_file" file does not exists.');
            }

            return $clientKeyFile;
        });
    }
}
