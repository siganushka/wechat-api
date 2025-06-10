<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\OAuth;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see https://developers.weixin.qq.com/doc/oplatform/Website_App/WeChat_Login/Wechat_Login.html
 */
class Qrcode extends Client
{
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('scope', 'snsapi_login');
        $resolver->setAllowedValues('scope', ['snsapi_login']);
    }

    protected function getBaseUrl(): string
    {
        return 'https://open.weixin.qq.com/connect/qrconnect';
    }
}
