<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\OptionsUtils;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_4
 */
class Refund extends AbstractRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

    /** @var EncoderInterface|DecoderInterface */
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::appid($resolver);
        OptionsUtils::mchid($resolver);
        OptionsUtils::mchkey($resolver);
        OptionsUtils::mch_client_cert($resolver);
        OptionsUtils::mch_client_key($resolver);
        OptionsUtils::sign_type($resolver);
        OptionsUtils::nonce_str($resolver);

        $resolver
            ->define('transaction_id')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('out_trade_no')
            ->default(null)
            ->allowedTypes('null', 'string')
            ->normalize(function (Options $options, ?string $outTradeNo) {
                if (null === $options['transaction_id'] && null === $outTradeNo) {
                    throw new MissingOptionsException('The required option "transaction_id" or "out_trade_no" is missing.');
                }

                return $outTradeNo;
            })
        ;

        $resolver
            ->define('out_refund_no')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('total_fee')
            ->required()
            ->allowedTypes('int')
        ;

        $resolver
            ->define('refund_fee')
            ->required()
            ->allowedTypes('int')
        ;

        $resolver
            ->define('refund_fee_type')
            ->default(null)
            ->allowedValues(null, 'CNY')
        ;

        $resolver
            ->define('refund_desc')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('refund_account')
            ->default(null)
            ->allowedValues(null, 'REFUND_SOURCE_UNSETTLED_FUNDS', 'REFUND_SOURCE_RECHARGE_FUNDS')
        ;

        $resolver
            ->define('notify_url')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $body = array_filter([
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'sign_type' => $options['sign_type'],
            'nonce_str' => $options['nonce_str'],
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => $options['out_trade_no'],
            'out_refund_no' => $options['out_refund_no'],
            'total_fee' => $options['total_fee'],
            'refund_fee' => $options['refund_fee'],
            'refund_fee_type' => $options['refund_fee_type'],
            'refund_desc' => $options['refund_desc'],
            'refund_account' => $options['refund_account'],
            'notify_url' => $options['notify_url'],
        ], fn ($value) => null !== $value);

        $signatureUtils = SignatureUtils::create();
        if (isset($this->configurators[ConfigurationOptions::class])) {
            $signatureUtils->using($this->configurators[ConfigurationOptions::class]);
        }

        // Generate signature
        $body['sign'] = $signatureUtils->generateFromOptions([
            'mchkey' => $options['mchkey'],
            'sign_type' => $options['sign_type'],
            'data' => $body,
        ]);

        $request
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setBody($this->serializer->encode($body, 'xml'))
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
