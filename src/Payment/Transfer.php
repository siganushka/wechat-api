<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\WechatOptions;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
 */
class Transfer extends AbstractRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    /** @var EncoderInterface|DecoderInterface */
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        WechatOptions::appid($resolver);
        WechatOptions::mchid($resolver);
        WechatOptions::mch_client_cert($resolver);
        WechatOptions::mch_client_key($resolver);
        WechatOptions::nonce_str($resolver);

        $resolver
            ->define('device_info')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('partner_trade_no')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('openid')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('check_name')
            ->default('NO_CHECK')
            ->allowedValues('NO_CHECK', 'FORCE_CHECK')
        ;

        $resolver
            ->define('re_user_name')
            ->default(null)
            ->allowedTypes('null', 'string')
            ->normalize(function (Options $options, ?string $reUserName) {
                if ('FORCE_CHECK' === $options['check_name'] && null === $reUserName) {
                    throw new MissingOptionsException('The required option "re_user_name" is missing (when "check_name" option is set to "FORCE_CHECK").');
                }

                return $reUserName;
            })
        ;

        $resolver
            ->define('amount')
            ->required()
            ->allowedTypes('int')
        ;

        $resolver
            ->define('desc')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('spbill_create_ip')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('scene')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('brand_id')
            ->default(null)
            ->allowedTypes('null', 'int')
        ;

        $resolver
            ->define('finder_template_id')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        foreach (['mchid', 'mch_client_cert', 'mch_client_key'] as $optionName) {
            if (null === $options[$optionName]) {
                throw new NoConfigurationException(sprintf('No configured value for "%s" option.', $optionName));
            }
        }

        $body = array_filter([
            'mch_appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'device_info' => $options['device_info'],
            'nonce_str' => $options['nonce_str'],
            'partner_trade_no' => $options['partner_trade_no'],
            'openid' => $options['openid'],
            'check_name' => $options['check_name'],
            're_user_name' => $options['re_user_name'],
            'amount' => $options['amount'],
            'desc' => $options['desc'],
            'spbill_create_ip' => $options['spbill_create_ip'],
            'scene' => $options['scene'],
            'brand_id' => $options['brand_id'],
            'finder_template_id' => $options['finder_template_id'],
        ], fn ($value) => null !== $value);

        $signatureUtils = SignatureUtils::create();
        if (isset($this->configurators[ConfigurationOptions::class])) {
            $signatureUtils->using($this->configurators[ConfigurationOptions::class]);
        }

        // Generate signature
        $body['sign'] = $signatureUtils->generate($body);
        $xmlBody = $this->serializer->encode($body, 'xml');

        $request
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setBody($xmlBody)
            ->setLocalCert($options['mch_client_cert'])
            ->setLocalPk($options['mch_client_key'])
        ;
    }

    protected function parseResponse(ResponseInterface $response): array
    {
        $result = $this->serializer->decode($response->getContent(), 'xml');

        $returnCode = (string) ($result['return_code'] ?? '');
        $resultCode = (string) ($result['result_code'] ?? '');

        if ('FAIL' === $returnCode) {
            throw new ParseResponseException($response, (string) ($result['return_msg'] ?? ''));
        }

        if ('FAIL' === $resultCode) {
            throw new ParseResponseException($response, (string) ($result['err_code_des'] ?? ''));
        }

        return $result;
    }
}
