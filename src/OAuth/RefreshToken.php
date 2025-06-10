<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\OAuth;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\OptionSet;
use Siganushka\ApiFactory\Wechat\ParseResponseTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractRequest<array>
 */
class RefreshToken extends AbstractRequest
{
    use ParseResponseTrait { responseAsArray as parseResponse; }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::appid($resolver);

        $resolver
            ->define('refresh_token')
            ->required()
            ->allowedTypes('string')
        ;
    }

    /**
     * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#2
     */
    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'appid' => $options['appid'],
            'refresh_token' => $options['refresh_token'],
            'grant_type' => 'refresh_token',
        ];

        $request
            ->setUrl('https://api.weixin.qq.com/sns/oauth2/refresh_token')
            ->setQuery($query)
        ;
    }
}
