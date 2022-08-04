<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Core;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_the_WeChat_server_IP_address.html
 */
class CallbackIp extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/getcallbackip';

    private AccessToken $accessToken;

    public function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $result = $this->accessToken->send();

        $query = [
            'access_token' => $result['access_token'],
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
