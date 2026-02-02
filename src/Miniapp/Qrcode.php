<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Miniapp;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\OptionSet;
use Siganushka\ApiFactory\Wechat\ParseResponseTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractRequest<string>
 */
class Qrcode extends AbstractRequest
{
    use ParseResponseTrait { responseAsImageContent as parseResponse; }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::token($resolver);

        $resolver
            ->define('path')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('width')
            ->default(null)
            ->allowedTypes('null', 'int')
        ;
    }

    /**
     * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.createQRCode.html
     */
    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['token'],
        ];

        $body = array_filter([
            'path' => $options['path'],
            'width' => $options['width'],
        ], static fn ($value) => null !== $value);

        $request
            ->setMethod('POST')
            ->setUrl('https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode')
            ->setQuery($query)
            ->setJson($body)
        ;
    }
}
