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
class CheckToken extends AbstractRequest
{
    use ParseResponseTrait { responseAsArray as parseResponse; }

    /**
     * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#4
     */
    public const URL = 'https://api.weixin.qq.com/sns/auth';

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
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['access_token'],
            'openid' => $options['openid'],
        ];

        $request
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }
}
