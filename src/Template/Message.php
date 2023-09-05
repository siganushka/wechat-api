<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Template;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\OptionSet;
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
        OptionSet::token($resolver);

        $resolver
            ->define('touser')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('template')
            ->required()
            ->allowedTypes(Template::class)
        ;

        $resolver
            ->define('url')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('miniprogram')
            ->default(function (OptionsResolver $miniprogramResolver): void {
                $miniprogramResolver->define('appid')->allowedTypes('string');
                $miniprogramResolver->define('pagepath')->allowedTypes('string');
            })
            ->allowedTypes('array')
        ;

        $resolver
            ->define('client_msg_id')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['token'],
        ];

        $body = array_filter([
            'touser' => $options['touser'],
            'template_id' => $options['template']->getId(),
            'url' => $options['url'],
            'miniprogram' => $options['miniprogram'],
            'data' => $options['template']->getData(),
            'client_msg_id' => $options['client_msg_id'],
        ], fn ($value) => null !== $value && [] !== $value);

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
