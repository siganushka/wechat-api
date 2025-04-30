<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Core;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Response\StaticResponse;
use Siganushka\ApiFactory\Wechat\OptionSet;
use Siganushka\ApiFactory\Wechat\ParseResponseTrait;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @extends AbstractRequest<array>
 */
class Ticket extends AbstractRequest
{
    use ParseResponseTrait { responseAsArray as parseResponse; }

    /**
     * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html#54
     */
    public const URL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

    private CacheItemPoolInterface $cachePool;

    public function __construct(?HttpClientInterface $httpClient = null, ?CacheItemPoolInterface $cachePool = null)
    {
        $this->cachePool = $cachePool ?? new FilesystemAdapter();

        parent::__construct($httpClient);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::token($resolver);

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
            if (\is_array($data = $cacheItem->get())) {
                return StaticResponse::createFromArray($data);
            }
        }

        $response = parent::sendRequest($request);
        $parsedResponse = $this->parseResponse($response);

        $cacheItem->set($parsedResponse);
        $cacheItem->expiresAfter($parsedResponse['expires_in'] ?? 7200);
        $this->cachePool->save($cacheItem);

        return $response;
    }
}
