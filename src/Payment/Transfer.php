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
 * @see https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
 */
class Transfer extends AbstractRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    /** @var EncoderInterface|DecoderInterface */
    private SerializerInterface $serializer;
    private array $defaultOptions;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;

        $this->defaultOptions = [
            'nonce_str' => GenericUtils::getNonceStr(),
            'check_name' => 'NO_CHECK',
            'device_info' => null,
            're_user_name' => null,
            'spbill_create_ip' => null,
            'scene' => null,
            'brand_id' => null,
            'finder_template_id' => null,
        ];
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        Configuration::apply($resolver);

        $resolver->setDefaults($this->defaultOptions);

        $resolver->setRequired(['partner_trade_no', 'openid', 'amount', 'desc']);

        $resolver->setAllowedTypes('amount', 'int');
        $resolver->setAllowedValues('check_name', ['NO_CHECK', 'FORCE_CHECK']);

        $resolver->setNormalizer('re_user_name', function (Options $options, ?string $reUserName) {
            if ('FORCE_CHECK' === $options['check_name'] && null === $reUserName) {
                throw new MissingOptionsException('The required option "re_user_name" is missing (when "check_name" option is set to "FORCE_CHECK").');
            }

            return $reUserName;
        });
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        foreach (['mchid', 'mch_client_cert', 'mch_client_key'] as $optionName) {
            if (null === $options[$optionName]) {
                throw new NoConfigurationException(sprintf('No configured value for "%s" option.', $optionName));
            }
        }

        $body = [
            'mch_appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'partner_trade_no' => $options['partner_trade_no'],
            'openid' => $options['openid'],
            'amount' => $options['amount'],
            'desc' => $options['desc'],
        ];

        foreach (array_keys($this->defaultOptions) as $optionName) {
            if (null !== $options[$optionName]) {
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
