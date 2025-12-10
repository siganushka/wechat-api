<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Message;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\OptionSet;
use Siganushka\ApiFactory\Wechat\ParseResponseTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractRequest<array>
 */
class TemplateMessage extends AbstractRequest
{
    use ParseResponseTrait { responseAsArray as parseResponse; }

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
            ->options(function (OptionsResolver $miniprogramResolver): void {
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

    /**
     * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html#5
     */
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
            ->setUrl('https://api.weixin.qq.com/cgi-bin/message/template/send')
            ->setQuery($query)
            ->setJson($body)
        ;
    }
}
