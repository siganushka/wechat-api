<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Miniapp;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.get.html
 */
class Wxacode extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/wxa/getwxacode';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['access_token', 'path']);
        $resolver->setDefaults([
            'env_version' => 'release',
            'width' => 430,
            'auto_color' => false,
            'is_hyaline' => false,
            'line_color' => function (OptionsResolver $lineColorResolver) {
                $lineColorResolver->setDefaults([
                    'r' => 0,
                    'g' => 0,
                    'b' => 0,
                ]);
            },
        ]);

        $resolver->setAllowedTypes('access_token', 'string');
        $resolver->setAllowedTypes('path', 'string');
        $resolver->setAllowedTypes('env_version', 'string');
        $resolver->setAllowedTypes('width', 'int');
        $resolver->setAllowedTypes('auto_color', 'bool');
        $resolver->setAllowedTypes('is_hyaline', 'bool');

        $resolver->setAllowedValues('env_version', ['release', 'trial', 'develop']);
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['access_token'],
        ];

        $body = [
            'path' => $options['path'],
            'env_version' => $options['env_version'],
            'width' => $options['width'],
            'auto_color' => $options['auto_color'],
            'is_hyaline' => $options['is_hyaline'],
            'line_color' => $options['line_color'],
        ];

        $request
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setQuery($query)
            ->setJson($body)
        ;
    }

    /**
     * @return string 小程序码二进制内容
     */
    protected function parseResponse(ResponseInterface $response): string
    {
        $headers = $response->getHeaders();
        if (str_contains($headers['content-type'][0] ?? '', 'json')) {
            /**
             * @var array{ errcode?: int, errmsg?: string }
             */
            $result = $response->toArray();

            $errcode = (int) ($result['errcode'] ?? 0);
            $errmsg = (string) ($result['errmsg'] ?? '');

            throw new ParseResponseException($response, $errmsg, $errcode);
        }

        return $response->getContent();
    }
}
