<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Miniapp;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.createQRCode.html
 */
class Qrcode extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['access_token', 'path']);
        $resolver->setDefined('width');

        $resolver->setAllowedTypes('access_token', 'string');
        $resolver->setAllowedTypes('path', 'string');
        $resolver->setAllowedTypes('width', 'int');
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['access_token'],
        ];

        $body = [
            'path' => $options['path'],
        ];

        if (isset($options['width'])) {
            $body['width'] = $options['width'];
        }

        $request
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setQuery($query)
            ->setJson($body)
        ;
    }

    /**
     * @return string 小程序小程序二维码二进制内容
     */
    protected function parseResponse(ResponseInterface $response): string
    {
        $headers = $response->getHeaders();
        if (str_contains($headers['content-type'][0] ?? '', 'image')) {
            return $response->getContent();
        }

        /**
         * @var array{ errcode?: int, errmsg?: string }
         */
        $result = $response->toArray();

        $errcode = (int) ($result['errcode'] ?? 0);
        $errmsg = (string) ($result['errmsg'] ?? '');

        throw new ParseResponseException($response, $errmsg, $errcode);
    }
}
