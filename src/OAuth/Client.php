<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\OAuth;

use Siganushka\ApiClient\OptionsExtendableInterface;
use Siganushka\ApiClient\OptionsExtendableTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat oauth client class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html
 */
class Client implements OptionsExtendableInterface
{
    use OptionsExtendableTrait;

    public const URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    public function getRedirectUrl(array $options = []): string
    {
        $resolved = $this->resolve($options);

        $query = [
            'appid' => $resolved['appid'],
            'redirect_uri' => $resolved['redirect_uri'],
            'scope' => $resolved['scope'],
            'response_type' => 'code',
        ];

        if ($resolved['state']) {
            $query['state'] = $resolved['state'];
        }

        ksort($query);

        return sprintf('%s?%s#wechat_redirect', static::URL, http_build_query($query));
    }

    public function redirect(array $options = []): void
    {
        header(sprintf('Location: %s', $this->getRedirectUrl($options)));
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['appid', 'redirect_uri']);

        $resolver->setDefault('state', null);
        $resolver->setDefault('scope', 'snsapi_base');

        $resolver->setAllowedTypes('appid', 'string');
        $resolver->setAllowedTypes('redirect_uri', 'string');
        $resolver->setAllowedTypes('state', ['null', 'string']);

        $resolver->setAllowedValues('scope', ['snsapi_base', 'snsapi_userinfo']);
    }
}
