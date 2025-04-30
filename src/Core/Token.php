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
class Token extends AbstractRequest
{
    use ParseResponseTrait { responseAsArray as parseResponse; }

    /**
     * @see https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_access_token.html
     */
    public const URL = 'https://api.weixin.qq.com/cgi-bin/token';

    private CacheItemPoolInterface $cachePool;

    public function __construct(?HttpClientInterface $httpClient = null, ?CacheItemPoolInterface $cachePool = null)
    {
        $this->cachePool = $cachePool ?? new FilesystemAdapter();

        parent::__construct($httpClient);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::appid($resolver);
        OptionSet::secret($resolver);
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
