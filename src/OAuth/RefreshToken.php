<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\OAuth;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\OptionSet;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#2
 */
class RefreshToken extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::appid($resolver);

        $resolver
            ->define('refresh_token')
            ->required()
            ->allowedTypes('string')
        ;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'appid' => $options['appid'],
            'refresh_token' => $options['refresh_token'],
            'grant_type' => 'refresh_token',
        ];

        $request
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }

    /**
     * @return array{
     *  openid: string,
     *  access_token: string,
     *  expires_in: int,
     *  refresh_token: string,
     *  scope: string
     * }
     */
    protected function parseResponse(ResponseInterface $response): array
    {
        $result = $response->toArray();

        $errcode = (int) ($result['errcode'] ?? 0);
        $errmsg = (string) ($result['errmsg'] ?? '');

        if (0 === $errcode) {
            return $result;
        }

        throw new ParseResponseException($response, $errmsg, $errcode);
    }
}
