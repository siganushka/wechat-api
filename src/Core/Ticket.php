<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Core;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Response\CachedResponse;
use Siganushka\ApiFactory\Wechat\OptionsUtils;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html#54
 */
class Ticket extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

    private CacheItemPoolInterface $cachePool;

    public function __construct(HttpClientInterface $httpClient = null, CacheItemPoolInterface $cachePool = null)
    {
        $this->cachePool = $cachePool ?? new FilesystemAdapter();

        parent::__construct($httpClient);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::token($resolver);

        $resolver
            ->define('type')
            ->default('jsapi')
            ->allowedValues('jsapi', 'wx_card')
        ;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['token'],
            'type' => $options['type'],
        ];

        $request
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }

    protected function sendRequest(RequestOptions $request): ResponseInterface
    {
        $cacheItem = $this->cachePool->getItem((string) $request);
        if ($cacheItem->isHit()) {
            return CachedResponse::createFromJson($cacheItem->get());
        }

        $response = parent::sendRequest($request);
        $parsedResponse = $this->parseResponse($response);

        $cacheItem->set($parsedResponse);
        $cacheItem->expiresAfter($parsedResponse['expires_in'] ?? 7200);
        $this->cachePool->save($cacheItem);

        return $response;
    }

    protected function parseResponse(ResponseInterface $response): array
    {
        $result = $response->toArray();

        $errcode = (int) ($result['errcode'] ?? 0);
        $errmsg = (string) ($result['errmsg'] ?? 'error');

        if (0 === $errcode && isset($result['ticket'])) {
            return $result;
        }

        throw new ParseResponseException($response, $errmsg, $errcode);
    }
}
