<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Core;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_access_token.html
 */
class AccessToken extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/token';

    private CacheItemPoolInterface $cachePool;
    private Configuration $configuration;

    public function __construct(CacheItemPoolInterface $cachePool, Configuration $configuration)
    {
        $this->cachePool = $cachePool;
        $this->configuration = $configuration;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'appid' => $this->configuration['appid'],
            'secret' => $this->configuration['secret'],
            'grant_type' => 'client_credential',
        ];

        $request
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }

    protected function sendRequest(RequestOptions $request): ResponseInterface
    {
        $key = sprintf('%s_%s', __CLASS__, md5(serialize($request->toArray())));

        $cacheItem = $this->cachePool->getItem($key);
        if ($cacheItem->isHit()) {
            return ResponseFactory::createMockResponseWithJson($cacheItem->get());
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
        $errmsg = (string) ($result['errmsg'] ?? '');

        if (0 === $errcode) {
            return $result;
        }

        throw new ParseResponseException($response, $errmsg, $errcode);
    }
}
