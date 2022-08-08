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
 * @see https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
 */
class Transfer extends AbstractRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    private SignatureUtils $signatureUtils;
    private array $defaultOptions;

    public function __construct(SignatureUtils $signatureUtils = null)
    {
        $this->signatureUtils = $signatureUtils ?? SignatureUtils::create();

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
        $resolver->setDefaults($this->defaultOptions);

        $resolver->setRequired(['appid', 'mchid', 'client_cert_file', 'client_key_file', 'partner_trade_no', 'openid', 'amount', 'desc']);

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
        foreach (['mchid', 'client_cert_file', 'client_key_file'] as $optionName) {
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

        // Extending resolvable from current class
        foreach ($this->resolvables as $resolvable) {
            $this->signatureUtils->extend($resolvable);
        }

        // Generate signature
        $body['sign'] = $this->signatureUtils->generate($body);

        $request
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setBody(SerializerUtils::xmlEncode($body))
            ->setLocalCert($options['client_cert_file'])
            ->setLocalPk($options['client_key_file'])
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
