<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Core;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\CacheableResponseInterface;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_access_token.html
 */
class AccessToken extends AbstractRequest implements CacheableResponseInterface
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/token';

    private Configuration $configuration;
    private int $cacheTtl = 7200;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    protected function configureRequest(array $options): void
    {
        $query = [
            'appid' => $this->configuration['appid'],
            'secret' => $this->configuration['secret'],
            'grant_type' => 'client_credential',
        ];

        $this
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
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
            $this->cacheTtl = (int) $result['expires_in'];

            return $result;
        }

        throw new ParseResponseException($response, $errmsg, $errcode);
    }

    public function getCacheTtl(): int
    {
        return $this->cacheTtl;
    }
}
