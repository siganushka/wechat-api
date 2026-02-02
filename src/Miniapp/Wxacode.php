<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Miniapp;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\OptionSet;
use Siganushka\ApiFactory\Wechat\ParseResponseTrait;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractRequest<string>
 */
class Wxacode extends AbstractRequest
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
            ->allowedValues(static fn (?string $value) => null === $value || preg_match('/^#[0-9a-f]{6}$/i', $value))
        ;

        $resolver
            ->define('line_color_value')
            ->default(static function (Options $options) {
                /** @var string|null */
                $lineColor = $options['line_color'];
                if (null === $lineColor) {
                    return null;
                }

                return array_map('hexdec', [
                    'r' => mb_substr($lineColor, 1, 2),
                    'g' => mb_substr($lineColor, 3, 2),
                    'b' => mb_substr($lineColor, 5, 2),
                ]);
            })
            ->allowedTypes('null', 'array')
        ;

        $resolver
            ->define('auto_color')
            ->default(static fn (Options $options) => null === $options['line_color'] && null === $options['line_color_value'] ? null : false)
            ->allowedTypes('null', 'bool')
        ;
    }

    /**
     * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.get.html
     */
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
        ], static fn ($value) => null !== $value);

        $request
            ->setMethod('POST')
            ->setUrl('https://api.weixin.qq.com/wxa/getwxacode')
            ->setQuery($query)
            ->setJson($body)
        ;
    }
}
