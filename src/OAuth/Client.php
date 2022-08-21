<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\OAuth;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\OptionsConfiguratorInterface;
use Siganushka\ApiClient\OptionsConfiguratorTrait;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\OptionsUtils;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Wechat oauth client class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html
 */
class Client implements OptionsConfiguratorInterface
{
    use OptionsConfiguratorTrait;

    public const URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    private HttpClientInterface $httpClient;
    private CacheItemPoolInterface $cachePool;

    public function __construct(HttpClientInterface $httpClient = null, CacheItemPoolInterface $cachePool = null)
    {
        $this->httpClient = $httpClient ?? HttpClient::create();
        $this->cachePool = $cachePool ?? new FilesystemAdapter();
    }

    public function getRedirectUrl(array $options = []): string
    {
        $resolver = new OptionsResolver();
        $this->configure($resolver);

        $resolved = $resolver->resolve($options);

        $query = [
            'appid' => $resolved['appid'],
            'redirect_uri' => $resolved['redirect_uri'],
            'scope' => $resolved['scope'],
            'response_type' => 'code',
        ];

        if ($resolved['state']) {
            $query['state'] = $resolved['state'];
        }

        ksort($query);

        return sprintf('%s?%s#wechat_redirect', static::URL, http_build_query($query));
    }

    public function getAccessToken(array $options = []): array
    {
        $accessToken = new AccessToken($this->cachePool);
        $accessToken->setHttpClient($this->httpClient);

        if (isset($this->extensions[ConfigurationOptions::class])) {
            $accessToken->using($this->extensions[ConfigurationOptions::class]);
        }

        return $accessToken->send($options);
    }

    public function getUserInfo(array $options = []): array
    {
        $userInfo = new UserInfo();
        $userInfo->setHttpClient($this->httpClient);

        return $userInfo->send($options);
    }

    public function refreshToken(array $options = []): array
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setHttpClient($this->httpClient);

        if (isset($this->extensions[ConfigurationOptions::class])) {
            $refreshToken->using($this->extensions[ConfigurationOptions::class]);
        }

        return $refreshToken->send($options);
    }

    public function checkToken(array $options = []): array
    {
        $checkToken = new CheckToken();
        $checkToken->setHttpClient($this->httpClient);

        return $checkToken->send($options);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::appid($resolver);

        $resolver
            ->define('redirect_uri')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('state')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('scope')
            ->default('snsapi_base')
            ->allowedValues('snsapi_base', 'snsapi_userinfo')
        ;
    }
}
