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
 * @see https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_access_token.html
 */
class Token extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/token';

    private CacheItemPoolInterface $cachePool;

    public function __construct(HttpClientInterface $httpClient = null, CacheItemPoolInterface $cachePool = null)
    {
        $this->cachePool = $cachePool ?? new FilesystemAdapter();

        parent::__construct($httpClient);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::appid($resolver);
        OptionsUtils::secret($resolver);
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'appid' => $options['appid'],
            'secret' => $options['secret'],
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

        if (0 === $errcode && isset($result['access_token'])) {
            return $result;
        }

        throw new ParseResponseException($response, $errmsg, $errcode);
    }
}
