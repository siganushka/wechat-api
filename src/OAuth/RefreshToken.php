<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\OAuth;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#2
 */
class RefreshToken extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    protected function configureRequest(array $options): void
    {
        $query = [
            'appid' => $this->configuration['appid'],
            'refresh_token' => $options['refresh_token'],
            'grant_type' => 'refresh_token',
        ];

        $this
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('refresh_token');
        $resolver->setAllowedTypes('refresh_token', 'string');
    }

    /**
     * @return array{
     *  access_token: string,
     *  expires_in: int,
     *  refresh_token: string,
     *  openid: string,
     *  scope: string,
     *  unionid?: string,
     *  errcode?: int,
     *  errmsg?: string
     * }
     */
    public function parseResponse(ResponseInterface $response): array
    {
        /**
         * @var array{
         *  access_token: string,
         *  expires_in: int,
         *  refresh_token: string,
         *  openid: string,
         *  scope: string,
         *  unionid?: string,
         *  errcode?: int,
         *  errmsg?: string
         * }
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
