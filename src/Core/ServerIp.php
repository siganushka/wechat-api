<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Core;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\OptionsUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_the_WeChat_server_IP_address.html
 */
class ServerIp extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/get_api_domain_ip';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::token($resolver);
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
        $result = $response->toArray();

        $errcode = (int) ($result['errcode'] ?? 0);
        $errmsg = (string) ($result['errmsg'] ?? '');

        if (0 === $errcode) {
            return $result['ip_list'] ?? [];
        }

        throw new ParseResponseException($response, $errmsg, $errcode);
    }
}
