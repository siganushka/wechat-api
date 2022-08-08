<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\OAuth;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#1
 */
class AccessToken extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['appid', 'secret', 'code']);

        $resolver->setAllowedTypes('code', 'string');
        $resolver->setAllowedTypes('appid', 'string');
        $resolver->setAllowedTypes('secret', 'string');
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'appid' => $options['appid'],
            'secret' => $options['secret'],
            'grant_type' => 'authorization_code',
            'code' => $options['code'],
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
            return $result;
        }

        throw new ParseResponseException($response, $errmsg, $errcode);
    }
}
