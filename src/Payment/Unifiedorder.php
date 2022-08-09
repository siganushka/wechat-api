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
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1
 */
class Unifiedorder extends AbstractRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    public const URL2 = 'https://api2.mch.weixin.qq.com/pay/unifiedorder';

    /** @var EncoderInterface|DecoderInterface */
    private SerializerInterface $serializer;
    private array $defaultOptions;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;

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

    protected function configureOptions(OptionsResolver $resolver): void
    {
        Configuration::apply($resolver);

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
        foreach (['mchid'] as $optionName) {
            if (null === $options[$optionName]) {
                throw new NoConfigurationException(sprintf('No configured value for "%s" option.', $optionName));
            }
        }

        $body = [
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'sign_type' => $options['sign_type'],
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
