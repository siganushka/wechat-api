<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Core;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_the_WeChat_server_IP_address.html
 */
class CallbackIp extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/getcallbackip';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('access_token');
        $resolver->setAllowedTypes('access_token', 'string');
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['access_token'],
        ];

        $request
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }

    /**
     * @return array<int, string>
     */
    protected function parseResponse(ResponseInterface $response)
    {
        /**
         * @var array{
         *  ip_list?: array<int, string>,
         *  errcode?: int,
         *  errmsg?: string
         * }
         */
        $result = $response->toArray();

        $errcode = (int) ($result['errcode'] ?? 0);
        $errmsg = (string) ($result['errmsg'] ?? '');

        if (0 === $errcode) {
            return $result['ip_list'] ?? [];
        }

        throw new ParseResponseException($response, $errmsg, $errcode);
    }
}
