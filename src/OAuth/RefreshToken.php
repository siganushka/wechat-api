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

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('refresh_token');
        $resolver->setDefault('using_open_api', false);

        $resolver->setAllowedTypes('refresh_token', 'string');
        $resolver->setAllowedTypes('using_open_api', 'bool');
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $appid = $options['using_open_api'] ? 'open_appid' : 'appid';
        if (null === $this->configuration[$appid]) {
            throw new NoConfigurationException(sprintf('No configured value for "%s" option.', $appid));
        }

        $query = [
            'appid' => $this->configuration[$appid],
            'refresh_token' => $options['refresh_token'],
            'grant_type' => 'refresh_token',
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
