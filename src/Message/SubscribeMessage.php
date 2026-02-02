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
class SubscribeMessage extends AbstractRequest
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
            ->define('page')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('miniprogram_state')
            ->default('formal')
            ->allowedTypes('string')
            ->allowedValues('developer', 'trial', 'formal')
        ;

        $resolver
            ->define('lang')
            ->default('zh_CN')
            ->allowedTypes('string')
            ->allowedValues('zh_CN', 'zh_TW', 'zh_HK', 'en_US')
        ;
    }

    /**
     * @see https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/subscribe-message/sendMessage.html
     */
    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $query = [
            'access_token' => $options['token'],
        ];

        /** @var Template */
        $template = $options['template'];
        $body = array_filter([
            'touser' => $options['touser'],
            'template_id' => $template->getId(),
            'data' => $template->getData(),
            'page' => $options['page'],
            'miniprogram_state' => $options['miniprogram_state'],
            'lang' => $options['lang'],
        ], static fn ($value) => null !== $value && [] !== $value);

        $request
            ->setMethod('POST')
            ->setUrl('https://api.weixin.qq.com/cgi-bin/message/subscribe/send')
            ->setQuery($query)
            ->setJson($body)
        ;
    }
}
