<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Ticket;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\CacheableResponseInterface;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html#54
 */
class Ticket extends AbstractRequest implements CacheableResponseInterface
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

    private int $cacheTtl = 7200;

    protected function configureRequest(array $options): void
    {
        $query = [
            'access_token' => $options['access_token'],
            'type' => $options['type'],
        ];

        $this
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('access_token');
        $resolver->setRequired('type');

        $resolver->setAllowedTypes('access_token', 'string');
        $resolver->setAllowedTypes('type', 'string');
    }

    /**
     * @return array{
     *  ticket: string,
     *  expires_in: int,
     *  errcode?: int,
     *  errmsg?: string
     * }
     */
    public function parseResponse(ResponseInterface $response): array
    {
        /**
         * @var array{
         *  ticket: string,
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
