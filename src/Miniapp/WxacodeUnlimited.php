<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Miniapp;

use Siganushka\ApiFactory\RequestOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WxacodeUnlimited extends Wxacode
{
    /**
     * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.getUnlimited.html
     */
    public const URL = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->remove('path');

        $resolver
            ->define('scene')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('page')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('check_path')
            ->default(null)
            ->allowedTypes('null', 'bool')
        ;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['token'],
        ];

        $body = array_filter([
            'scene' => $options['scene'],
            'page' => $options['page'],
            'check_path' => $options['check_path'],
            'env_version' => $options['env_version'],
            'width' => $options['width'],
            'auto_color' => $options['auto_color'],
            'is_hyaline' => $options['is_hyaline'],
            'line_color' => $options['line_color_value'],
        ], fn ($value) => null !== $value);

        $request
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setQuery($query)
            ->setJson($body)
        ;
    }
}
