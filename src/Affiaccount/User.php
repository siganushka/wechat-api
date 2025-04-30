<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Affiaccount;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\OptionSet;
use Siganushka\ApiFactory\Wechat\ParseResponseTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @extends AbstractRequest<array>
 */
class User extends AbstractRequest
{
    use ParseResponseTrait;

    /**
     * @see https://developers.weixin.qq.com/doc/offiaccount/User_Management/Getting_a_User_List.html
     */
    public const URL = 'https://api.weixin.qq.com/cgi-bin/user/get';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::token($resolver);
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['token'],
        ];

        $request
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }

    protected function parseResponse(ResponseInterface $response): array
    {
        return $this->responseAsArray($response)['data']['openid'] ?? [];
    }
}
