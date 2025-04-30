<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\OAuth;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\ParseResponseTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractRequest<array>
 */
class UserInfo extends AbstractRequest
{
    use ParseResponseTrait { responseAsArray as parseResponse; }

    /**
     * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#3
     */
    public const URL = 'https://api.weixin.qq.com/sns/userinfo';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->define('access_token')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('openid')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('lang')
            ->default('zh_CN')
            ->allowedTypes('string')
            ->allowedValues('zh_CN', 'zh_TW', 'en')
        ;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['access_token'],
            'openid' => $options['openid'],
            'lang' => $options['lang'],
        ];

        $request
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }
}
