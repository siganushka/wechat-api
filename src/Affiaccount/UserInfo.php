<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Affiaccount;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\OptionSet;
use Siganushka\ApiFactory\Wechat\ParseResponseTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractRequest<array>
 */
class UserInfo extends AbstractRequest
{
    use ParseResponseTrait { responseAsArray as parseResponse; }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::token($resolver);

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

    /**
     * @see https://developers.weixin.qq.com/doc/offiaccount/User_Management/Get_users_basic_information_UnionID.html#UinonId
     */
    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['token'],
            'openid' => $options['openid'],
            'lang' => $options['lang'],
        ];

        $request
            ->setUrl('https://api.weixin.qq.com/cgi-bin/user/info')
            ->setQuery($query)
        ;
    }
}
