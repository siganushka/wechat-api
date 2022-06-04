<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\OAuth;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#4
 */
class CheckToken extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/sns/auth';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('access_token');
        $resolver->setRequired('openid');

        $resolver->setAllowedTypes('access_token', 'string');
        $resolver->setAllowedTypes('openid', 'string');
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

    /**
     * @return array{ errcode?: int, errmsg?: string }
     */
    public function parseResponse(ResponseInterface $response): array
    {
        /**
         * @var array{ errcode?: int, errmsg?: string }
         */
        $result = $response->toArray();

        $errcode = (int) ($result['errcode'] ?? 0);
        $errmsg = (string) ($result['errmsg'] ?? '');

        if (0 === $errcode) {
            return $result;
        }

        throw new ParseResponseException($response, $errmsg, $errcode);
    }
}
