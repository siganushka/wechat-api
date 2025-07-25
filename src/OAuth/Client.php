<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\OAuth;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiFactory\ResolverInterface;
use Siganushka\ApiFactory\ResolverTrait;
use Siganushka\ApiFactory\Wechat\ConfigurationExtension;
use Siganushka\ApiFactory\Wechat\OptionSet;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html
 */
class Client implements ResolverInterface
{
    use ResolverTrait;

    public function __construct(private readonly ?HttpClientInterface $httpClient = null, private readonly ?CacheItemPoolInterface $cachePool = null)
    {
    }

    public function getRedirectUrl(array $options = []): string
    {
        $resolved = $this->resolve($options);
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

        return \sprintf('%s?%s#wechat_redirect', $this->getBaseUrl(), http_build_query($query));
    }

    public function getAccessToken(array $options = []): array
    {
        $accessToken = new AccessToken($this->httpClient, $this->cachePool);

        if (isset($this->extensions[ConfigurationExtension::class])) {
            $accessToken->extend($this->extensions[ConfigurationExtension::class]);
        }

        return $accessToken->send($options);
    }

    public function getUserInfo(array $options = []): array
    {
        $userInfo = new UserInfo($this->httpClient);

        return $userInfo->send($options);
    }

    public function refreshToken(array $options = []): array
    {
        $refreshToken = new RefreshToken($this->httpClient);

        if (isset($this->extensions[ConfigurationExtension::class])) {
            $refreshToken->extend($this->extensions[ConfigurationExtension::class]);
        }

        return $refreshToken->send($options);
    }

    public function checkToken(array $options = []): array
    {
        $checkToken = new CheckToken($this->httpClient);

        return $checkToken->send($options);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::appid($resolver);

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

    protected function getBaseUrl(): string
    {
        return 'https://open.weixin.qq.com/connect/oauth2/authorize';
    }
}
