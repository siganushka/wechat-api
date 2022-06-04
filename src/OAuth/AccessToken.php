<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\OAuth;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#1
 */
class AccessToken extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('code');
        $resolver->setDefault('using_open_api', false);

        $resolver->setAllowedTypes('code', 'string');
        $resolver->setAllowedTypes('using_open_api', 'bool');
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $appid = $options['using_open_api'] ? 'open_appid' : 'appid';
        $secret = $options['using_open_api'] ? 'open_secret' : 'secret';

        if (null === $this->configuration[$appid]) {
            throw new NoConfigurationException(sprintf('No configured value for "%s" option.', $appid));
        }

        $query = [
            'appid' => $this->configuration[$appid],
            'secret' => $this->configuration[$secret],
            'grant_type' => 'authorization_code',
            'code' => $options['code'],
        ];

        $request
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
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
