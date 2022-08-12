<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\OAuth;

use Siganushka\ApiClient\OptionsResolvableInterface;
use Siganushka\ApiClient\OptionsResolvableTrait;
use Siganushka\ApiClient\Wechat\WechatOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat oauth client class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html
 */
class Client implements OptionsResolvableInterface
{
    use OptionsResolvableTrait;

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
        WechatOptions::appid($resolver);

        $resolver
            ->define('redirect_uri')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('state')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('scope')
            ->default('snsapi_base')
            ->allowedValues('snsapi_base', 'snsapi_userinfo')
        ;
    }
}
