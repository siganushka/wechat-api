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
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1
 */
class Unifiedorder extends AbstractRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    public const URL2 = 'https://api2.mch.weixin.qq.com/pay/unifiedorder';

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
            'spbill_create_ip' => GenericUtils::getClientIp(),
            'openid' => null,
            'product_id' => null,
            'device_info' => null,
            'detail' => null,
            'attach' => null,
            'fee_type' => null,
            'time_start' => null,
            'time_expire' => null,
            'goods_tag' => null,
            'limit_pay' => null,
            'receipt' => null,
            'profit_sharing' => null,
            'scene_info' => null,
            'using_slave_api' => false,
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults($this->defaultOptions);
        $resolver->setRequired(['body', 'out_trade_no', 'total_fee', 'trade_type', 'notify_url']);
        $resolver->setAllowedTypes('total_fee', 'int');
        $resolver->setAllowedTypes('using_slave_api', 'bool');

        $resolver->setAllowedValues('fee_type', [null, 'CNY']);
        $resolver->setAllowedValues('trade_type', [null, 'JSAPI', 'NATIVE', 'APP', 'MWEB']);
        $resolver->setAllowedValues('limit_pay', [null, 'no_credit']);
        $resolver->setAllowedValues('receipt', [null, 'Y']);
        $resolver->setAllowedValues('profit_sharing', [null, 'Y', 'N']);

        $resolver->setNormalizer('openid', function (Options $options, ?string $openid) {
            if ('JSAPI' === $options['trade_type'] && null === $openid) {
                throw new MissingOptionsException('The required option "openid" is missing (when "trade_type" option is set to "JSAPI").');
            }

            return $openid;
        });

        $resolver->setNormalizer('product_id', function (Options $options, ?string $productId) {
            if ('NATIVE' === $options['trade_type'] && null === $productId) {
                throw new MissingOptionsException('The required option "product_id" is missing (when "trade_type" option is set to "NATIVE").');
            }

            return $productId;
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
            'body' => $options['body'],
            'out_trade_no' => $options['out_trade_no'],
            'total_fee' => $options['total_fee'],
            'trade_type' => $options['trade_type'],
            'notify_url' => $options['notify_url'],
        ];

        $ignoreOptions = ['using_slave_api'];
        foreach (array_keys($this->defaultOptions) as $optionName) {
            if (null !== $options[$optionName] && !\in_array($optionName, $ignoreOptions)) {
                $body[$optionName] = $options[$optionName];
            }
        }

        $signatureUtils = new SignatureUtils($this->configuration);
        $body['sign'] = $signatureUtils->generate($body);

        $xmlBody = SerializerUtils::xmlEncode($body);
        $apiURL = $options['using_slave_api'] ? static::URL2 : static::URL;

        $request
            ->setMethod('POST')
            ->setUrl($apiURL)
            ->setBody($xmlBody)
        ;
    }

    public function parseResponse(ResponseInterface $response)
    {
        /**
         * @var array{
         *  return_code?: string,
         *  result_code?: string,
         *  return_msg?: string,
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
