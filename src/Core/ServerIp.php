<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Core;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_the_WeChat_server_IP_address.html
 */
class ServerIp extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/get_api_domain_ip';

    protected function configureRequest(array $options): void
    {
        $query = [
            'access_token' => $options['access_token'],
        ];

        $this
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('access_token');
        $resolver->setAllowedTypes('access_token', 'string');
    }

    /**
     * @return array<int, string>
     */
    public function parseResponse(ResponseInterface $response): array
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
