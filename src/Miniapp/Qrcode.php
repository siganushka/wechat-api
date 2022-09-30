<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Miniapp;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\OptionsUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.createQRCode.html
 */
class Qrcode extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::token($resolver);

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

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['token'],
        ];

        $body = array_filter([
            'path' => $options['path'],
            'width' => $options['width'],
        ], fn ($value) => null !== $value);

        $request
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setQuery($query)
            ->setJson($body)
        ;
    }

    protected function parseResponse(ResponseInterface $response): string
    {
        $headers = $response->getHeaders();
        if (str_contains($headers['content-type'][0] ?? '', 'image')) {
            return $response->getContent();
        }

        $result = $response->toArray();

        $errcode = (int) ($result['errcode'] ?? 0);
        $errmsg = (string) ($result['errmsg'] ?? '');

        throw new ParseResponseException($response, $errmsg, $errcode);
    }
}
