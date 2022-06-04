<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Miniapp;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/login/auth.code2Session.html
 */
class SessionKey extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/sns/jscode2session';

    private CacheItemPoolInterface $cachePool;
    private Configuration $configuration;

    public function __construct(CacheItemPoolInterface $cachePool, Configuration $configuration)
    {
        $this->cachePool = $cachePool;
        $this->configuration = $configuration;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('code');
        $resolver->setAllowedTypes('code', 'string');
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'appid' => $this->configuration['appid'],
            'secret' => $this->configuration['secret'],
            'grant_type' => 'authorization_code',
            'code' => $options['code'],
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
            /** @var array{ openid: string, session_key: string } */
            $cacheData = $cacheItem->get();

            return ResponseFactory::createMockResponseWithJson($cacheData);
        }

        $response = parent::sendRequest($request);
        $parsedResponse = $this->parseResponse($response);

        $cacheItem->set($parsedResponse);
        $cacheItem->expiresAfter(300);
        $this->cachePool->save($cacheItem);

        return $response;
    }

    /**
     * @return array{
     *  openid?: string,
     *  session_key?: string,
     *  errcode?: int,
     *  errmsg?: string
     * }
     */
    public function parseResponse(ResponseInterface $response): array
    {
        /**
         * @var array{
         *  openid?: string,
         *  session_key?: string,
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
