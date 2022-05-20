<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Payment;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\HelperSet;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
 */
class Transfer extends AbstractRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    private Configuration $configuration;
    private XmlEncoder $xmlEncoder;

    /**
     * @var array<string, mixed>
     */
    private array $defaultOptions = [];

    public function __construct(Configuration $configuration, XmlEncoder $xmlEncoder)
    {
        $this->configuration = $configuration;
        $this->xmlEncoder = $xmlEncoder;
        $this->defaultOptions = [
            'nonce_str' => HelperSet::getNonceStr(),
            'check_name' => 'NO_CHECK',
            'device_info' => null,
            're_user_name' => null,
            'spbill_create_ip' => null,
            'scene' => null,
            'brand_id' => null,
            'finder_template_id' => null,
        ];
    }

    protected function configureRequest(array $options): void
    {
        foreach (['mchid', 'mchkey', 'client_cert_file', 'client_key_file'] as $optionName) {
            if (null === $this->configuration[$optionName]) {
                throw new NoConfigurationException(sprintf('No configured value for "%s" option.', $optionName));
            }
        }

        $body = [
            'mch_appid' => $this->configuration['appid'],
            'mchid' => $this->configuration['mchid'],
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

        $signatureUtils = new SignatureUtils($this->configuration);
        $body['sign'] = $signatureUtils->generate($body);

        $xmlBody = $this->xmlEncoder->encode($body, 'xml');

        $this
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setBody($xmlBody)
            ->setLocalCert($this->configuration['client_cert_file'])
            ->setLocalPk($this->configuration['client_key_file'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
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
        $result = $this->xmlEncoder->decode($response->getContent(), 'xml');

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
