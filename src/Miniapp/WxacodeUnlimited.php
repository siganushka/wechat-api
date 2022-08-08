<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Miniapp;

use Siganushka\ApiClient\RequestOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.getUnlimited.html
 */
class WxacodeUnlimited extends Wxacode
{
    public const URL = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->remove('path');

        $resolver->setRequired('scene');
        $resolver->setDefined(['page', 'check_path']);
        $resolver->setAllowedTypes('scene', 'string');
        $resolver->setAllowedTypes('page', 'string');
        $resolver->setAllowedTypes('check_path', 'bool');
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['access_token'],
        ];

        $body = [
            'scene' => $options['scene'],
        ];

        foreach (['page', 'check_path', 'env_version', 'width', 'auto_color', 'is_hyaline'] as $option) {
            if (isset($options[$option])) {
                $body[$option] = $options[$option];
            }
        }

        if ($options['line_color']) {
            $body['line_color'] = $options['line_color'];
        }

        $request
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setQuery($query)
            ->setJson($body)
        ;
    }
}
