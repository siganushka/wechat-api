<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Template;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html#5
 */
class Message extends AbstractRequest
{
    public const URL = 'https://api.weixin.qq.com/cgi-bin/message/template/send';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['access_token', 'touser', 'template']);
        $resolver->setDefault('url', null);
        $resolver->setDefault('miniprogram', function (OptionsResolver $miniprogramResolver) {
            $miniprogramResolver->setDefined(['appid', 'pagepath']);
        });

        $resolver->setAllowedTypes('access_token', 'string');
        $resolver->setAllowedTypes('touser', 'string');
        $resolver->setAllowedTypes('template', Template::class);
        $resolver->setAllowedTypes('url', ['null', 'string']);
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['access_token'],
        ];

        $body = [
            'touser' => $options['touser'],
            'template_id' => $options['template']->getId(),
            'data' => $options['template']->getData(),
        ];

        foreach (['url', 'miniprogram'] as $field) {
            if ($options[$field]) {
                $body[$field] = $options[$field];
            }
        }

        $request
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setQuery($query)
            ->setJson($body)
        ;
    }

    protected function parseResponse(ResponseInterface $response): array
    {
        $result = $response->toArray();

        $errcode = (int) ($result['errcode'] ?? 0);
        $errmsg = (string) ($result['errmsg'] ?? '');

        if (0 === $errcode) {
            return $result;
        }

        throw new ParseResponseException($response, $errmsg, $errcode);
    }
}
