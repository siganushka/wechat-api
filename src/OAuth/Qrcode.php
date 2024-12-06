<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\OAuth;

use Symfony\Component\OptionsResolver\OptionsResolver;

class Qrcode extends Client
{
    /**
     * @see https://developers.weixin.qq.com/doc/oplatform/Website_App/WeChat_Login/Wechat_Login.html
     */
    public const URL = 'https://open.weixin.qq.com/connect/qrconnect';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('scope', 'snsapi_login');
        $resolver->setAllowedValues('scope', ['snsapi_login']);
    }
}
