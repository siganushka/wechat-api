<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Core;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\OptionSet;
use Siganushka\ApiFactory\Wechat\ParseResponseTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @extends AbstractRequest<array>
 */
class CallbackIp extends AbstractRequest
{
    use ParseResponseTrait;

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::token($resolver);
    }

    /**
     * @see https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_the_WeChat_server_IP_address.html
     */
    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['token'],
        ];

        $request
            ->setUrl('https://api.weixin.qq.com/cgi-bin/getcallbackip')
            ->setQuery($query)
        ;
    }

    protected function parseResponse(ResponseInterface $response): array
    {
        return $this->responseAsArray($response)['ip_list'] ?? [];
    }
}
