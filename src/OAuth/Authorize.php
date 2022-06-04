<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\OAuth;

use Siganushka\ApiClient\ConfigurableOptionsInterface;
use Siganushka\ApiClient\ConfigurableOptionsTrait;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat oauth authorize class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html
 * @see https://developers.weixin.qq.com/doc/oplatform/Website_App/WeChat_Login/Wechat_Login.html
 */
class Authorize implements ConfigurableOptionsInterface
{
    use ConfigurableOptionsTrait;

    public const URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    public const URL2 = 'https://open.weixin.qq.com/connect/qrconnect';

    protected Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function getAuthorizeUrl(array $options = []): string
    {
        $resolved = $this->resolve($options);

        $appid = $resolved['using_open_api'] ? 'open_appid' : 'appid';
        if (null === $this->configuration[$appid]) {
            throw new NoConfigurationException(sprintf('No configured value for "%s" option.', $appid));
        }

        $query = [
            'appid' => $this->configuration[$appid],
            'redirect_uri' => $resolved['redirect_uri'],
            'scope' => $resolved['scope'],
            'response_type' => 'code',
        ];

        if ($resolved['state']) {
            $query['state'] = $resolved['state'];
        }

        ksort($query);

        return ($resolved['using_open_api'] ? static::URL2 : static::URL).'?'.http_build_query($query).'#wechat_redirect';
    }

    /**
     * @param array<string, mixed> $options
     */
    public function redirect(array $options = []): void
    {
        $authorizeUrl = $this->getAuthorizeUrl($options);
        if (class_exists(RedirectResponse::class)) {
            $response = new RedirectResponse($authorizeUrl);
            $response->send();
        }

        header(sprintf('Location: %s', $authorizeUrl));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('redirect_uri');
        $resolver->setDefault('state', null);
        $resolver->setDefault('using_open_api', false);
        $resolver->setDefault('scope', function (Options $options) {
            return $options['using_open_api'] ? 'snsapi_login' : 'snsapi_base';
        });

        $resolver->setAllowedTypes('redirect_uri', 'string');
        $resolver->setAllowedTypes('scope', 'string');
        $resolver->setAllowedTypes('state', ['null', 'string']);
        $resolver->setAllowedTypes('using_open_api', 'bool');

        $resolver->setAllowedValues('scope', ['snsapi_base', 'snsapi_userinfo', 'snsapi_login']);
    }
}
