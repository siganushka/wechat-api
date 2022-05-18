<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Payment\Unifiedorder;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;

class UnifiedorderTest extends TestCase
{
    public function testAll(): void
    {
        $request = static::createRequest();
        static::assertNull($request->getMethod());
        static::assertNull($request->getUrl());
        static::assertEquals([], $request->getOptions());

        $options = [
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ];

        $request->build($options);
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(Unifiedorder::URL, $request->getUrl());

        /**
         * @var array{ body: string }
         */
        $options2 = $request->getOptions();
        static::assertArrayHasKey('body', $options2);

        /**
         * @var array{
         *  body: string,
         *  out_trade_no: string,
         *  total_fee: int,
         *  trade_type: string,
         *  notify_url: string,
         *  openid: string
         * }
         */
        $data = ConfigurationTest::createXmlEncoder()->decode($options2['body'], 'xml');
        static::assertArrayHasKey('appid', $data);
        static::assertArrayHasKey('mch_id', $data);
        static::assertArrayHasKey('sign_type', $data);
        static::assertArrayHasKey('nonce_str', $data);
        static::assertArrayHasKey('spbill_create_ip', $data);
        static::assertArrayHasKey('sign', $data);
        static::assertEquals($options['body'], $data['body']);
        static::assertEquals($options['out_trade_no'], $data['out_trade_no']);
        static::assertEquals($options['total_fee'], $data['total_fee']);
        static::assertEquals($options['trade_type'], $data['trade_type']);
        static::assertEquals($options['notify_url'], $data['notify_url']);
        static::assertEquals($options['openid'], $data['openid']);
    }

    public function testWithOptions(): void
    {
        $options = [
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
            'nonce_str' => 'test_nonce_str',
            'spbill_create_ip' => 'test_spbill_create_ip',
            'product_id' => 'test_product_id',
            'device_info' => 'test_device_info',
            'detail' => 'test_detail',
            'attach' => 'test_attach',
            'fee_type' => 'CNY',
            'time_start' => 'test_time_start',
            'time_expire' => 'test_time_expire',
            'goods_tag' => 'test_goods_tag',
            'limit_pay' => 'no_credit',
            'receipt' => 'Y',
            'profit_sharing' => 'Y',
            'scene_info' => 'test_scene_info',
            'using_slave_api' => true,
        ];

        $request = static::createRequest();
        $request->build($options);
        static::assertEquals(Unifiedorder::URL2, $request->getUrl());

        /**
         * @var array{ body: string }
         */
        $options2 = $request->getOptions();
        static::assertArrayHasKey('body', $options2);

        /**
         * @var array{
         *  body: string,
         *  out_trade_no: string,
         *  total_fee: int,
         *  trade_type: string,
         *  notify_url: string,
         *  openid: string
         * }
         */
        $data = ConfigurationTest::createXmlEncoder()->decode($options2['body'], 'xml');
        static::assertArrayHasKey('appid', $data);
        static::assertArrayHasKey('mch_id', $data);
        static::assertArrayHasKey('sign_type', $data);
        static::assertArrayHasKey('nonce_str', $data);
        static::assertArrayHasKey('spbill_create_ip', $data);
        static::assertArrayHasKey('sign', $data);

        unset($options['using_slave_api']);
        foreach ($options as $key => $value) {
            static::assertEquals($value, $data[$key]);
        }
    }

    public function testMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required options "body", "notify_url", "out_trade_no", "total_fee", "trade_type" are missing.');

        $request = static::createRequest();
        $request->build();
    }

    public function testOpenidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "openid" is missing (when "trade_type" option is set to "JSAPI")');

        $request = static::createRequest();
        $request->build([
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
        ]);
    }

    public function testProductIdMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "product_id" is missing (when "trade_type" option is set to "NATIVE")');

        $request = static::createRequest();
        $request->build([
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'NATIVE',
        ]);
    }

    public function testFeeTypeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "fee_type" with value "foo" is invalid. Accepted values are: null, "CNY"');

        $request = static::createRequest();
        $request->build([
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
            'fee_type' => 'foo',
        ]);
    }

    public function testTradeTypeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "trade_type" with value "foo" is invalid. Accepted values are: null, "JSAPI", "NATIVE", "APP", "MWEB"');

        $request = static::createRequest();
        $request->build([
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'foo',
            'openid' => 'test_openid',
        ]);
    }

    public function testLimitPayInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "limit_pay" with value "foo" is invalid. Accepted values are: null, "no_credit"');

        $request = static::createRequest();
        $request->build([
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
            'limit_pay' => 'foo',
        ]);
    }

    public function testReceiptInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "receipt" with value "foo" is invalid. Accepted values are: null, "Y"');

        $request = static::createRequest();
        $request->build([
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
            'receipt' => 'foo',
        ]);
    }

    public function testProfitSharingInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "profit_sharing" with value "foo" is invalid. Accepted values are: null, "Y", "N"');

        $request = static::createRequest();
        $request->build([
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
            'profit_sharing' => 'foo',
        ]);
    }

    public function testTotalFeeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "total_fee" with value "test_total_fee" is expected to be of type "int", but is of type "string"');

        $request = static::createRequest();
        $request->build([
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 'test_total_fee',
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
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

        $encoder = ConfigurationTest::createXmlEncoder();
        $request = new Unifiedorder($configuration, $encoder);
        $request->build([
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
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

        $encoder = ConfigurationTest::createXmlEncoder();
        $request = new Unifiedorder($configuration, $encoder);
        $request->build([
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ]);
    }

    public function testParseResponse(): void
    {
        $data = [
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
            'mch_id' => 'test_mch_id',
            'appid' => 'test_appid',
            'prepay_id' => 'test_prepay_id',
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

    public static function createRequest(): Unifiedorder
    {
        $configuration = ConfigurationTest::createConfiguration();
        $encoder = ConfigurationTest::createXmlEncoder();

        return new Unifiedorder($configuration, $encoder);
    }
}
