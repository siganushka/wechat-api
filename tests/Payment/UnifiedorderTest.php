<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Payment\Unifiedorder;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UnifiedorderTest extends TestCase
{
    public function testResolve(): void
    {
        $options = [
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ];

        $unifiedorder = static::createRequest();

        $resolved = $unifiedorder->resolve($options);
        static::assertArrayHasKey('nonce_str', $resolved);
        static::assertSame('0.0.0.0', $resolved['spbill_create_ip']);
        static::assertSame('JSAPI', $resolved['trade_type']);
        static::assertSame('test_openid', $resolved['openid']);
        static::assertSame(1, $resolved['total_fee']);
        static::assertSame('test_body', $resolved['body']);
        static::assertSame('test_notify_url', $resolved['notify_url']);
        static::assertSame('test_out_trade_no', $resolved['out_trade_no']);
        static::assertSame([
            'nonce_str', 'spbill_create_ip', 'openid', 'product_id',
            'device_info', 'detail', 'attach', 'fee_type', 'time_start',
            'time_expire', 'goods_tag', 'limit_pay', 'receipt',
            'profit_sharing', 'scene_info', 'using_slave_api', 'body',
            'out_trade_no', 'total_fee', 'trade_type', 'notify_url',
        ], $unifiedorder->getResolver()->getDefinedOptions());
    }

    public function testSend(): void
    {
        $options = [
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ];

        $responseData = [
            'appid' => 'foo',
            'mch_id' => 'bar',
            'nonce_str' => 'baz',
            'sign' => 'test_sign',
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
        ];

        $encoder = ConfigurationTest::createXmlEncoder();
        $response = ResponseFactory::createMockResponse($encoder->encode($responseData, 'xml'));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $unifiedorder = static::createRequest();
        $unifiedorder->setHttpClient($httpClient);

        $parsedResponse = $unifiedorder->send($options);
        static::assertSame($responseData, $parsedResponse);
    }

    public function testConfigureRequest(): void
    {
        $options = [
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ];

        $unifiedorder = static::createRequest();
        $request = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($unifiedorder, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($unifiedorder, $request, $unifiedorder->resolve($options));

        static::assertSame('POST', $request->getMethod());
        static::assertSame(Unifiedorder::URL, $request->getUrl());

        $requestOptions = $request->toArray();
        $encoder = ConfigurationTest::createXmlEncoder();

        /**
         * @var array{
         *  nonce_str: string,
         *  sign: string,
         *  appid: string,
         *  mch_id: string,
         *  sign_type: string,
         *  body: string,
         *  out_trade_no: string,
         *  total_fee: string,
         *  trade_type: string,
         *  notify_url: string,
         *  spbill_create_ip: string,
         *  openid: string
         * }
         */
        $body = $encoder->decode($requestOptions['body'], 'xml');
        static::assertArrayHasKey('nonce_str', $body);
        static::assertArrayHasKey('sign', $body);
        static::assertSame('test_appid', $body['appid']);
        static::assertSame('test_mchid', $body['mch_id']);
        static::assertSame('HMAC-SHA256', $body['sign_type']);
        static::assertSame('test_body', $body['body']);
        static::assertSame('test_out_trade_no', $body['out_trade_no']);
        static::assertSame('1', $body['total_fee']);
        static::assertSame('JSAPI', $body['trade_type']);
        static::assertSame('test_notify_url', $body['notify_url']);
        static::assertSame('0.0.0.0', $body['spbill_create_ip']);
        static::assertSame('test_openid', $body['openid']);

        $customOptions = [
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
            'profit_sharing' => 'N',
            'scene_info' => 'test_scene_info',
            'using_slave_api' => true,
        ];

        $configureRequestRef->invoke($unifiedorder, $request, $unifiedorder->resolve($options + $customOptions));
        $requestOptions = $request->toArray();
        static::assertSame(Unifiedorder::URL2, $request->getUrl());

        /**
         * @var array{
         *  product_id: string,
         *  device_info: string,
         *  detail: string,
         *  attach: string,
         *  fee_type: string,
         *  time_start: string,
         *  time_expire: string,
         *  goods_tag: string,
         *  limit_pay: string,
         *  receipt: string,
         *  profit_sharing: string,
         *  scene_info: string
         * }
         */
        $body = $encoder->decode($requestOptions['body'], 'xml');
        static::assertSame('test_product_id', $body['product_id']);
        static::assertSame('test_device_info', $body['device_info']);
        static::assertSame('test_detail', $body['detail']);
        static::assertSame('test_attach', $body['attach']);
        static::assertSame('CNY', $body['fee_type']);
        static::assertSame('test_time_start', $body['time_start']);
        static::assertSame('test_time_expire', $body['time_expire']);
        static::assertSame('test_goods_tag', $body['goods_tag']);
        static::assertSame('no_credit', $body['limit_pay']);
        static::assertSame('Y', $body['receipt']);
        static::assertSame('N', $body['profit_sharing']);
        static::assertSame('test_scene_info', $body['scene_info']);
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

        $encoder = ConfigurationTest::createXmlEncoder();
        $response = ResponseFactory::createMockResponse($encoder->encode($responseData, 'xml'));

        $unifiedorder = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($unifiedorder, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($unifiedorder, $response);
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

        $encoder = ConfigurationTest::createXmlEncoder();
        $response = ResponseFactory::createMockResponse($encoder->encode($responseData, 'xml'));

        $unifiedorder = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($unifiedorder, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($unifiedorder, $response);
    }

    public function testMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required options "body", "notify_url", "out_trade_no", "total_fee", "trade_type" are missing.');

        $unifiedorder = static::createRequest();
        $unifiedorder->resolve();
    }

    public function testOpenidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "openid" is missing (when "trade_type" option is set to "JSAPI")');

        $unifiedorder = static::createRequest();
        $unifiedorder->resolve([
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

        $unifiedorder = static::createRequest();
        $unifiedorder->resolve([
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

        $unifiedorder = static::createRequest();
        $unifiedorder->resolve([
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

        $unifiedorder = static::createRequest();
        $unifiedorder->resolve([
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

        $unifiedorder = static::createRequest();
        $unifiedorder->resolve([
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

        $unifiedorder = static::createRequest();
        $unifiedorder->resolve([
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

        $unifiedorder = static::createRequest();
        $unifiedorder->resolve([
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

        $unifiedorder = static::createRequest();
        $unifiedorder->resolve([
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
        $unifiedorder = new Unifiedorder($encoder, $configuration);
        $unifiedorder->send([
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
        $unifiedorder = new Unifiedorder($encoder, $configuration);
        $unifiedorder->send([
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ]);
    }

    public static function createRequest(): Unifiedorder
    {
        $configuration = ConfigurationTest::createConfiguration();
        $encoder = ConfigurationTest::createXmlEncoder();

        return new Unifiedorder($encoder, $configuration);
    }
}
