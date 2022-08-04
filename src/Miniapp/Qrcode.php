<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Miniapp;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.createQRCode.html
 */
class Qrcode extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode';

    private AccessToken $accessToken;

    public function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $result = $this->accessToken->send();

        $resolver->setRequired('path');
        $resolver->setDefined('width');

        $resolver->setAllowedTypes('path', 'string');
        $resolver->setAllowedTypes('width', 'int');
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $result = $this->accessToken->send();

        $query = [
            'access_token' => $result['access_token'],
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
