<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Siganushka\ApiClient\AbstractConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Configuration extends AbstractConfiguration
{
    protected function configureOptions(OptionsResolver $resolver): void
    {
        static::apply($resolver);
    }

    public static function apply(OptionsResolver $resolver): void
    {
        WechatOptions::appid($resolver);
        WechatOptions::secret($resolver);
        WechatOptions::mchid($resolver);
        WechatOptions::mchkey($resolver);
        WechatOptions::mch_client_cert($resolver);
        WechatOptions::mch_client_key($resolver);
    }
}
