<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Core\Request;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\CacheableResponseInterface;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_access_token.html
 */
class AccessTokenRequest extends AbstractRequest implements CacheableResponseInterface
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/token';

    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    protected function configureRequest(array $options): void
    {
        $query = [
            'appid' => $this->configuration['appid'],
            'secret' => $this->configuration['appsecret'],
            'grant_type' => $options['grant_type'],
        ];

        $this
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('grant_type', 'client_credential');
        $resolver->setAllowedTypes('grant_type', 'string');
    }

    /**
     * @return array{ access_token: string, expires_in: int }
     */
    public function parseResponse(ResponseInterface $response): array
    {
        /**
         * @var array{
         *  access_token: string,
         *  expires_in: int,
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

    public function getCacheTtl(): int
    {
        return 7200;
    }
}
