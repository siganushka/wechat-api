<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\OAuth;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\ConfigurableSubjectInterface;
use Siganushka\ApiClient\ConfigurableSubjectTrait;
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
class Client implements ConfigurableSubjectInterface
{
    use ConfigurableSubjectTrait;

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
        if (isset($this->configurators[ConfigurationOptions::class])) {
            $accessToken->using($this->configurators[ConfigurationOptions::class]);
        }

        return $accessToken->send($this->httpClient, $options);
    }

    public function getUserInfo(array $options = []): array
    {
        $userInfo = new UserInfo();

        return $userInfo->send($this->httpClient, $options);
    }

    public function refreshToken(array $options = []): array
    {
        $refreshToken = new RefreshToken();
        if (isset($this->configurators[ConfigurationOptions::class])) {
            $refreshToken->using($this->configurators[ConfigurationOptions::class]);
        }

        return $refreshToken->send($this->httpClient, $options);
    }

    public function checkToken(array $options = []): array
    {
        $checkToken = new CheckToken();

        return $checkToken->send($this->httpClient, $options);
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
