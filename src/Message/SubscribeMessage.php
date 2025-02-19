<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Message;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\RequestOptions;
use Siganushka\ApiFactory\Wechat\OptionSet;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @extends AbstractRequest<array>
 */
class SubscribeMessage extends AbstractRequest
{
    /**
     * @see https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/subscribe-message/sendMessage.html
     */
    public const URL = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send';

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
