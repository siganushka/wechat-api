<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Payment\Refund;
use Siganushka\ApiClient\Wechat\SerializerUtils;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;

class RefundTest extends TestCase
{
    public function testResolve(): void
    {
        $options = [
            'transaction_id' => 'test_transaction_id',
            'out_trade_no' => 'test_out_trade_no',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        $request = static::createRequest();

        $resolved = $request->resolve($options);
        static::assertArrayHasKey('nonce_str', $resolved);
        static::assertSame('test_out_trade_no', $resolved['out_trade_no']);
        static::assertSame('test_transaction_id', $resolved['transaction_id']);
        static::assertSame('test_out_refund_no', $resolved['out_refund_no']);
        static::assertSame(12, $resolved['total_fee']);
        static::assertSame(10, $resolved['refund_fee']);
    }

    public function testBuild(): void
    {
        $options = [
            'transaction_id' => 'test_transaction_id',
            'out_trade_no' => 'test_out_trade_no',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        $request = static::createRequest();
        $requestOptions = $request->build($options);

        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Refund::URL, $requestOptions->getUrl());

        $body = SerializerUtils::xmlDecode($requestOptions->toArray()['body']);

        static::assertArrayHasKey('nonce_str', $body);
        static::assertArrayHasKey('sign', $body);
        static::assertSame('test_appid', $body['appid']);
        static::assertSame('test_mchid', $body['mch_id']);
        static::assertSame('HMAC-SHA256', $body['sign_type']);
        static::assertSame('test_transaction_id', $body['transaction_id']);

        $requestOptions = $request->build([
            'out_trade_no' => 'test_out_trade_no',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
            'refund_fee_type' => 'CNY',
            'refund_desc' => 'test_refund_desc',
            'refund_account' => 'REFUND_SOURCE_UNSETTLED_FUNDS',
            'notify_url' => 'test_notify_url',
        ]);

        $body = SerializerUtils::xmlDecode($requestOptions->toArray()['body']);
        static::assertArrayHasKey('nonce_str', $body);
        static::assertArrayHasKey('sign', $body);
        static::assertSame('test_appid', $body['appid']);
        static::assertSame('test_mchid', $body['mch_id']);
        static::assertSame('HMAC-SHA256', $body['sign_type']);
        static::assertSame('test_out_trade_no', $body['out_trade_no']);
        static::assertSame('CNY', $body['refund_fee_type']);
        static::assertSame('test_refund_desc', $body['refund_desc']);
        static::assertSame('REFUND_SOURCE_UNSETTLED_FUNDS', $body['refund_account']);
        static::assertSame('test_notify_url', $body['notify_url']);
    }

    public function testSend(): void
    {
        $options = [
            'transaction_id' => 'test_transaction_id',
            'out_trade_no' => 'test_out_trade_no',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        $responseData = [
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
        ];

        $xml = SerializerUtils::xmlEncode($responseData);
        $response = ResponseFactory::createMockResponse($xml);
        $httpClient = new MockHttpClient($response);

        $request = static::createRequest();
        $request->setHttpClient($httpClient);

        $result = $request->send($options);
        static::assertSame($responseData, $result);
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

        $request = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($request, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($request, $response);
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

        $request = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($request, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($request, $response);
    }

    public function testMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required options "out_refund_no", "refund_fee", "total_fee" are missing');

        $request = static::createRequest();
        $request->resolve();
    }

    public function testNumberMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "transaction_id" or "out_trade_no" is missing');

        $request = static::createRequest();
        $request->resolve([
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ]);
    }

    public function testRefundFeeTypeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "refund_fee_type" with value "foo" is invalid. Accepted values are: null, "CNY"');

        $request = static::createRequest();
        $request->resolve([
            'out_trade_no' => 'test_out_trade_no',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
            'refund_fee_type' => 'foo',
        ]);
    }

    public function testRefundAccountInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "refund_account" with value "foo" is invalid. Accepted values are: null, "REFUND_SOURCE_UNSETTLED_FUNDS", "REFUND_SOURCE_RECHARGE_FUNDS"');

        $request = static::createRequest();
        $request->resolve([
            'out_trade_no' => 'test_out_trade_no',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
            'refund_account' => 'foo',
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

        $request = static::createRequest($configuration);
        $request->send([
            'out_trade_no' => 'test_out_trade_no',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
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

        $request = static::createRequest($configuration);
        $request->send([
            'out_trade_no' => 'test_out_trade_no',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ]);
    }

    public static function createRequest(Configuration $configuration = null): Refund
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::createConfiguration();
        }

        return new Refund($configuration);
    }
}
