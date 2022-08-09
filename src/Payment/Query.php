<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\ConfigurationExtension;
use Siganushka\ApiClient\Wechat\GenericUtils;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_2
 */
class Query extends AbstractRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/pay/orderquery';
    public const URL2 = 'https://api2.mch.weixin.qq.com/pay/orderquery';

    /** @var EncoderInterface|DecoderInterface */
    private SerializerInterface $serializer;
    private array $defaultOptions;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->defaultOptions = [
            'nonce_str' => GenericUtils::getNonceStr(),
            'transaction_id' => null,
            'out_trade_no' => null,
            'using_slave_api' => false,
        ];
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        Configuration::apply($resolver);

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
            if (null === $options[$optionName]) {
                throw new NoConfigurationException(sprintf('No configured value for "%s" option.', $optionName));
            }
        }

        $body = [
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'sign_type' => $options['sign_type'],
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

        $signatureUtils = SignatureUtils::create();
        if (isset($this->extensions[ConfigurationExtension::class])) {
            $signatureUtils->extend($this->extensions[ConfigurationExtension::class]);
        }

        // Generate signature
        $body['sign'] = $signatureUtils->generate($body);
        $xmlBody = $this->serializer->encode($body, 'xml');

        $request
            ->setMethod('POST')
            ->setUrl($options['using_slave_api'] ? static::URL2 : static::URL)
            ->setBody($xmlBody)
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
