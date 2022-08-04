<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Ticket;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html#54
 */
class Ticket extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

    private CacheItemPoolInterface $cachePool;
    private AccessToken $accessToken;

    public function __construct(CacheItemPoolInterface $cachePool, AccessToken $accessToken)
    {
        $this->cachePool = $cachePool;
        $this->accessToken = $accessToken;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('type', 'jsapi');
        $resolver->setAllowedValues('type', ['jsapi', 'wx_card']);
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $result = $this->accessToken->send();

        $query = [
            'access_token' => $result['access_token'],
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
