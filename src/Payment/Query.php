<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Wechat\Utils\GenericUtils;
use Siganushka\ApiClient\Wechat\Utils\SerializerUtils;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_2
 */
class Query extends AbstractRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/pay/orderquery';
    public const URL2 = 'https://api2.mch.weixin.qq.com/pay/orderquery';

    private array $defaultOptions;

    public function __construct()
    {
        $this->defaultOptions = [
            'nonce_str' => GenericUtils::getNonceStr(),
            'transaction_id' => null,
            'out_trade_no' => null,
            'using_slave_api' => false,
        ];
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults($this->defaultOptions);
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

        $ignoreOptions = ['using_slave_api'];
        foreach (array_keys($this->defaultOptions) as $optionName) {
            if (null !== $options[$optionName] && !\in_array($optionName, $ignoreOptions)) {
                $body[$optionName] = $options[$optionName];
            }
        }

        if (isset($body['out_trade_no']) && isset($body['transaction_id'])) {
            unset($body['out_trade_no']);
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

    protected function parseResponse(ResponseInterface $response): array
    {
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
