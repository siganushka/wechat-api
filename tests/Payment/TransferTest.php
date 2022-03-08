<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Payment\Transfer;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;

class TransferTest extends TestCase
{
    public function testAll(): void
    {
        $request = static::createRequest();
        static::assertNull($request->getMethod());
        static::assertNull($request->getUrl());
        static::assertEquals([], $request->getOptions());

        $options = [
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ];

        $request->build($options);
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(Transfer::URL, $request->getUrl());

        /**
         * @var array{ body: string, local_cert: string, local_pk: string }
         */
        $options2 = $request->getOptions();
        static::assertArrayHasKey('body', $options2);
        static::assertArrayHasKey('local_cert', $options2);
        static::assertArrayHasKey('local_pk', $options2);

        /**
         * @var array{
         *  partner_trade_no: string,
         *  openid: string,
         *  amount: int,
         *  desc: string
         * }
         */
        $data = ConfigurationTest::createXmlEncoder()->decode($options2['body'], 'xml');
        static::assertArrayHasKey('mch_appid', $data);
        static::assertArrayHasKey('mchid', $data);
        static::assertArrayHasKey('nonce_str', $data);
        static::assertArrayHasKey('check_name', $data);
        static::assertArrayHasKey('sign', $data);
        static::assertEquals($options['partner_trade_no'], $data['partner_trade_no']);
        static::assertEquals($options['openid'], $data['openid']);
        static::assertEquals($options['amount'], $data['amount']);
        static::assertEquals($options['desc'], $data['desc']);

        $configuration = ConfigurationTest::createConfiguration();
        static::assertEquals($configuration['client_cert_file'], $options2['local_cert']);
        static::assertEquals($configuration['client_key_file'], $options2['local_pk']);
    }

    public function testWithOptions(): void
    {
        $options = [
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
            'nonce_str' => 'test_nonce_str',
            'check_name' => 'FORCE_CHECK',
            'device_info' => 'test_device_info',
            're_user_name' => 'test_re_user_name',
            'spbill_create_ip' => 'test_spbill_create_ip',
            'scene' => 'test_scene',
            'brand_id' => 'test_brand_id',
            'finder_template_id' => 'test_finder_template_id',
        ];

        $request = static::createRequest();
        $request->build($options);

        /**
         * @var array{ body: string, local_cert: string, local_pk: string }
         */
        $options2 = $request->getOptions();
        static::assertArrayHasKey('body', $options2);
        static::assertArrayHasKey('local_cert', $options2);
        static::assertArrayHasKey('local_pk', $options2);

        /**
         * @var array{
         *  partner_trade_no: string,
         *  openid: string,
         *  amount: int,
         *  desc: string
         * }
         */
        $data = ConfigurationTest::createXmlEncoder()->decode($options2['body'], 'xml');
        static::assertArrayHasKey('mch_appid', $data);
        static::assertArrayHasKey('mchid', $data);
        static::assertArrayHasKey('sign', $data);

        foreach ($options as $key => $value) {
            static::assertEquals($value, $data[$key]);
        }
    }

    public function testMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required options "amount", "desc", "openid", "partner_trade_no" are missing');

        $request = static::createRequest();
        $request->build();
    }

    public function testReUserNameMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "re_user_name" is missing (when "check_name" option is set to "FORCE_CHECK")');

        $request = static::createRequest();
        $request->build([
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

        $request = static::createRequest();
        $request->build([
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

        $request = static::createRequest();
        $request->build([
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
            'appsecret' => 'test_appsecret',
        ]);

        $encoder = ConfigurationTest::createXmlEncoder();
        $request = new Transfer($configuration, $encoder);
        $request->build([
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
            'appsecret' => 'test_appsecret',
            'mchid' => 'test_mchid',
        ]);

        $encoder = ConfigurationTest::createXmlEncoder();
        $request = new Transfer($configuration, $encoder);
        $request->build([
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
            'appsecret' => 'test_appsecret',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
        ]);

        $encoder = ConfigurationTest::createXmlEncoder();
        $request = new Transfer($configuration, $encoder);
        $request->build([
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
            'appsecret' => 'test_appsecret',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'client_cert_file' => __DIR__.'/../Mock/cert.pem',
        ]);

        $encoder = ConfigurationTest::createXmlEncoder();
        $request = new Transfer($configuration, $encoder);
        $request->build([
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ]);
    }

    public function testParseResponse(): void
    {
        $data = [
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
            'mch_appid' => 'test_mch_appid',
            'mchid' => 'test_mchid',
            'partner_trade_no' => 'test_partner_trade_no',
            'payment_no' => 'test_payment_no',
            'payment_time' => 'test_payment_time',
        ];

        /** @var string */
        $body = ConfigurationTest::createXmlEncoder()->encode($data, 'xml');
        $response = ResponseFactory::createMockResponse($body);

        $request = static::createRequest();
        static::assertEquals($data, $request->parseResponse($response));
    }

    public function testParseResponseReturnCodeException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionMessage('test_return_msg');

        $data = [
            'return_code' => 'FAIL',
            'return_msg' => 'test_return_msg',
        ];

        /** @var string */
        $body = ConfigurationTest::createXmlEncoder()->encode($data, 'xml');
        $response = ResponseFactory::createMockResponse($body);

        $request = static::createRequest();
        $request->parseResponse($response);
    }

    public function testParseResponseResultCodeException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionMessage('test_err_code_des');

        $data = [
            'result_code' => 'FAIL',
            'err_code_des' => 'test_err_code_des',
        ];

        /** @var string */
        $body = ConfigurationTest::createXmlEncoder()->encode($data, 'xml');
        $response = ResponseFactory::createMockResponse($body);

        $request = static::createRequest();
        $request->parseResponse($response);
    }

    public static function createRequest(): Transfer
    {
        $configuration = ConfigurationTest::createConfiguration();
        $encoder = ConfigurationTest::createXmlEncoder();

        return new Transfer($configuration, $encoder);
    }
}
