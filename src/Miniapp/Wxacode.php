<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Miniapp;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Wechat\WechatOptions;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.get.html
 */
class Wxacode extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/wxa/getwxacode';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        WechatOptions::token($resolver);

        $resolver
            ->define('path')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('env_version')
            ->default(null)
            ->allowedValues(null, 'release', 'trial', 'develop')
        ;

        $resolver
            ->define('width')
            ->default(null)
            ->allowedTypes('null', 'int')
        ;

        $resolver
            ->define('is_hyaline')
            ->default(null)
            ->allowedTypes('null', 'bool')
        ;

        $resolver
            ->define('line_color')
            ->default(null)
            ->allowedTypes('null', 'string')
            ->allowedValues(function (?string $value) {
                return null === $value || preg_match('/^#[0-9a-f]{6}$/i', $value);
            })
        ;

        $resolver
            ->define('line_color_value')
            ->default(function (Options $options) {
                if (null === $options['line_color']) {
                    return null;
                }

                return array_map('hexdec', [
                    'r' => mb_substr($options['line_color'], 1, 2),
                    'g' => mb_substr($options['line_color'], 3, 2),
                    'b' => mb_substr($options['line_color'], 5, 2),
                ]);
            })
            ->allowedTypes('null', 'array')
        ;

        $resolver
            ->define('auto_color')
            ->default(function (Options $options) {
                return null === $options['line_color'] ? null : false;
            })
            ->allowedTypes('null', 'bool')
        ;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['token'],
        ];

        $body = array_filter([
            'path' => $options['path'],
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
