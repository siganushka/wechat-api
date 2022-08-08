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

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['access_token', 'path']);
        $resolver->setDefined(['env_version', 'width', 'auto_color', 'is_hyaline']);
        $resolver->setDefault('line_color', function (OptionsResolver $lineColorResolver) {
            $lineColorResolver->setDefined(['r', 'g', 'b']);
            $lineColorResolver->setAllowedTypes('r', 'int');
            $lineColorResolver->setAllowedTypes('g', 'int');
            $lineColorResolver->setAllowedTypes('b', 'int');
        });

        $resolver->setAllowedTypes('access_token', 'string');
        $resolver->setAllowedTypes('path', 'string');
        $resolver->setAllowedTypes('env_version', 'string');
        $resolver->setAllowedTypes('width', 'int');
        $resolver->setAllowedTypes('auto_color', 'bool');
        $resolver->setAllowedTypes('is_hyaline', 'bool');
        $resolver->setAllowedTypes('line_color', 'array');

        $resolver->setAllowedValues('env_version', ['release', 'trial', 'develop']);
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['access_token'],
        ];

        $body = [
            'path' => $options['path'],
        ];

        foreach (['env_version', 'width', 'auto_color', 'is_hyaline'] as $option) {
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
