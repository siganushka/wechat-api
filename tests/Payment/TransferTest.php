<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Payment\Transfer;
use Siganushka\ApiClient\Wechat\SerializerUtils;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TransferTest extends TestCase
{
    public function testResolve(): void
    {
        $options = [
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ];

        $transfer = static::createRequest();

        $resolved = $transfer->resolve($options);
        static::assertArrayHasKey('nonce_str', $resolved);
        static::assertSame('NO_CHECK', $resolved['check_name']);
        static::assertSame('test_partner_trade_no', $resolved['partner_trade_no']);
        static::assertSame('test_openid', $resolved['openid']);
        static::assertSame(1, $resolved['amount']);
        static::assertSame('test_desc', $resolved['desc']);
        static::assertSame([
            'nonce_str',
            'check_name',
            'device_info',
            're_user_name',
            'spbill_create_ip',
            'scene',
            'brand_id',
            'finder_template_id',
            'partner_trade_no',
            'openid',
            'amount',
            'desc',
        ], $transfer->getResolver()->getDefinedOptions());
    }

    public function testSend(): void
    {
        $options = [
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ];

        $responseData = [
            'mch_appid' => 'foo',
            'mchid' => 'bar',
            'nonce_str' => 'baz',
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
        ];

        $response = ResponseFactory::createMockResponse(SerializerUtils::xmlEncode($responseData));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $transfer = static::createRequest();
        $transfer->setHttpClient($httpClient);

        $parsedResponse = $transfer->send($options);
        static::assertSame($responseData, $parsedResponse);
    }

    public function testConfigureRequest(): void
    {
        $options = [
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ];

        $transfer = static::createRequest();
        $request = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($transfer, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($transfer, $request, $transfer->resolve($options));

        static::assertSame('POST', $request->getMethod());
        static::assertSame(Transfer::URL, $request->getUrl());

        $configuration = ConfigurationTest::createConfiguration();
        $requestOptions = $request->toArray();
        static::assertSame($configuration['client_cert_file'], $requestOptions['local_cert']);
        static::assertSame($configuration['client_key_file'], $requestOptions['local_pk']);

        /**
         * @var array{
         *  nonce_str: string,
         *  sign: string,
         *  check_name: string,
         *  partner_trade_no: string,
         *  openid: string,
         *  mch_appid: string,
         *  mchid: string,
         *  amount: string,
         *  desc: string
         * }
         */
        $body = SerializerUtils::xmlDecode($requestOptions['body']);
        static::assertArrayHasKey('nonce_str', $body);
        static::assertArrayHasKey('sign', $body);
        static::assertSame('NO_CHECK', $body['check_name']);
        static::assertSame('test_partner_trade_no', $body['partner_trade_no']);
        static::assertSame('test_openid', $body['openid']);
        static::assertSame('test_appid', $body['mch_appid']);
        static::assertSame('test_mchid', $body['mchid']);
        static::assertSame('1', $body['amount']);
        static::assertSame('test_desc', $body['desc']);

        $customOptions = [
            'device_info' => 'test_device_info',
            're_user_name' => 'test_re_user_name',
            'spbill_create_ip' => 'test_spbill_create_ip',
            'scene' => 'test_scene',
            'brand_id' => 'test_brand_id',
            'finder_template_id' => 'test_finder_template_id',
        ];

        $configureRequestRef->invoke($transfer, $request, $transfer->resolve($options + $customOptions));
        $requestOptions = $request->toArray();

        /**
         * @var array{
         *  device_info: string,
         *  re_user_name: string,
         *  spbill_create_ip: string,
         *  scene: string,
         *  brand_id: string,
         *  finder_template_id: string
         * }
         */
        $body = SerializerUtils::xmlDecode($requestOptions['body']);
        static::assertSame('test_device_info', $body['device_info']);
        static::assertSame('test_re_user_name', $body['re_user_name']);
        static::assertSame('test_spbill_create_ip', $body['spbill_create_ip']);
        static::assertSame('test_scene', $body['scene']);
        static::assertSame('test_brand_id', $body['brand_id']);
        static::assertSame('test_finder_template_id', $body['finder_template_id']);
    }

    public function testReturnCodeParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test_return_msg');

        $responseData = [
            'return_code' => 'FAIL',
            'return_msg' => 'test_return_msg',
        ];

        $xml = SerializerUtils::xmlEncode($responseData);
        $response = ResponseFactory::createMockResponse($xml);

        $transfer = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($transfer, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($transfer, $response);
    }

    public function testResultCodeParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test_err_code_des');

        $responseData = [
            'result_code' => 'FAIL',
            'err_code_des' => 'test_err_code_des',
        ];

        $xml = SerializerUtils::xmlEncode($responseData);
        $response = ResponseFactory::createMockResponse($xml);

        $transfer = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($transfer, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($transfer, $response);
    }

    public function testMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required options "amount", "desc", "openid", "partner_trade_no" are missing');

        $transfer = static::createRequest();
        $transfer->resolve();
    }

    public function testReUserNameMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "re_user_name" is missing (when "check_name" option is set to "FORCE_CHECK")');

        $transfer = static::createRequest();
        $transfer->resolve([
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
            'check_name' => 'FORCE_CHECK',
        ]);
    }

    public function testCheckNameInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "check_name" with value "test_check_name" is invalid. Accepted values are: "NO_CHECK", "FORCE_CHECK"');

        $transfer = static::createRequest();
        $transfer->resolve([
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
            'check_name' => 'test_check_name',
        ]);
    }

    public function testAmountInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "amount" with value "test_amount" is expected to be of type "int", but is of type "string"');

        $transfer = static::createRequest();
        $transfer->resolve([
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 'test_amount',
            'desc' => 'test_desc',
        ]);
    }

    public function testMchidNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "mchid" option');

        $configuration = new Configuration([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
        ]);

        $transfer = new Transfer($configuration);
        $transfer->send([
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ]);
    }

    public function testMchkeyNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "mchkey" option');

        $configuration = new Configuration([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mchid' => 'test_mchid',
        ]);

        $transfer = new Transfer($configuration);
        $transfer->send([
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ]);
    }

    public function testClientCertFileNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "client_cert_file" option');

        $configuration = new Configuration([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
        ]);

        $transfer = new Transfer($configuration);
        $transfer->send([
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ]);
    }

    public function testClientKeyFileNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "client_key_file" option');

        $configuration = new Configuration([
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'client_cert_file' => __DIR__.'/../Mock/cert.pem',
        ]);

        $transfer = new Transfer($configuration);
        $transfer->send([
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ]);
    }

    public static function createRequest(): Transfer
    {
        $configuration = ConfigurationTest::createConfiguration();

        return new Transfer($configuration);
    }
}
