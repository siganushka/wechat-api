<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\OAuth;

use Siganushka\ApiClient\ConfigurableOptionsInterface;
use Siganushka\ApiClient\ConfigurableOptionsTrait;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat oauth authorize class.
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#0
 */
class Authorize implements ConfigurableOptionsInterface
{
    use ConfigurableOptionsTrait;

    public const URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    protected Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param array<string, string> $options
     */
    public function getAuthorizeUrl(array $options = []): string
    {
        $resolved = $this->resolveOptions($options);
        $resolved['appid'] = $this->configuration['appid'];
        $resolved['response_type'] = 'code';

        ksort($resolved);

        return static::URL.'?'.http_build_query($resolved).'#wechat_redirect';
    }

    /**
     * @param array<string, string> $options
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

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('redirect_uri');
        $resolver->setDefault('scope', 'snsapi_base');
        $resolver->setDefined('state');

        $resolver->setAllowedTypes('redirect_uri', 'string');
        $resolver->setAllowedTypes('scope', 'string');
        $resolver->setAllowedTypes('state', 'string');

        $resolver->setAllowedValues('scope', ['snsapi_base', 'snsapi_userinfo']);
    }
}
