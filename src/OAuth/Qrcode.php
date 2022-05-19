<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\OAuth;

use Siganushka\ApiClient\ConfigurableOptionsInterface;
use Siganushka\ApiClient\ConfigurableOptionsTrait;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat Open oauth authorize class.
 *
 * @see https://developers.weixin.qq.com/doc/oplatform/Website_App/WeChat_Login/Wechat_Login.html
 */
class Qrcode implements ConfigurableOptionsInterface
{
    use ConfigurableOptionsTrait;

    public const URL = 'https://open.weixin.qq.com/connect/qrconnect';

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
        if (null === $this->configuration['open_appid']) {
            throw new NoConfigurationException('No configured value for "open_appid" option.');
        }

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
        $resolver->setDefault('scope', 'snsapi_login');
        $resolver->setDefined(['lang', 'state']);

        $resolver->setAllowedTypes('redirect_uri', 'string');
        $resolver->setAllowedTypes('scope', 'string');
        $resolver->setAllowedTypes('lang', 'string');
        $resolver->setAllowedTypes('state', 'string');
    }
}
