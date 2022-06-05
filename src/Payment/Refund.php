<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\GenericUtils;
use Siganushka\ApiClient\Wechat\SerializerUtils;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_4
 */
class Refund extends AbstractRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

    private Configuration $configuration;

    /**
     * @var array<string, mixed>
     */
    private array $defaultOptions;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->defaultOptions = [
            'nonce_str' => GenericUtils::getNonceStr(),
            'transaction_id' => null,
            'out_trade_no' => null,
            'refund_fee_type' => null,
            'refund_desc' => null,
            'refund_account' => null,
            'notify_url' => null,
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults($this->defaultOptions);
        $resolver->setRequired(['out_refund_no', 'total_fee', 'refund_fee']);

        $resolver->setAllowedTypes('total_fee', 'int');
        $resolver->setAllowedTypes('refund_fee', 'int');

        $resolver->setAllowedValues('refund_fee_type', [null, 'CNY']);
        $resolver->setAllowedValues('refund_account', [null, 'REFUND_SOURCE_UNSETTLED_FUNDS', 'REFUND_SOURCE_RECHARGE_FUNDS']);

        $resolver->setNormalizer('transaction_id', function (Options $options, ?string $transactionId) {
            if (null === $options['out_trade_no'] && null === $transactionId) {
                throw new MissingOptionsException('The required option "transaction_id" or "out_trade_no" is missing.');
            }

            return $transactionId;
        });
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        foreach (['mchid', 'mchkey'] as $optionName) {
            if (null === $this->configuration[$optionName]) {
                throw new NoConfigurationException(sprintf('No configured value for "%s" option.', $optionName));
            }
        }

        $body = [
            'appid' => $this->configuration['appid'],
            'mch_id' => $this->configuration['mchid'],
            'sign_type' => $this->configuration['sign_type'],
        ];

        foreach (array_keys($this->defaultOptions) as $optionName) {
            if (null !== $options[$optionName]) {
                $body[$optionName] = $options[$optionName];
            }
        }

        if (isset($body['out_trade_no']) && isset($body['transaction_id'])) {
            unset($body['out_trade_no']);
        }

        $signatureUtils = new SignatureUtils($this->configuration);
        $body['sign'] = $signatureUtils->generate($body);

        $request
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setBody(SerializerUtils::xmlEncode($body))
        ;
    }

    protected function parseResponse(ResponseInterface $response)
    {
        /**
         * @var array{
         *  return_code?: string,
         *  return_msg?: string,
         *  result_code?: string,
         *  err_code_des?: string
         * }
         */
        $result = SerializerUtils::xmlDecode($response->getContent());

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
