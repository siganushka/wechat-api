<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\RequestOptions;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Payment\Refund;
use Siganushka\ApiClient\Wechat\SerializerUtils;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

        $refund = static::createRequest();

        $resolved = $refund->resolve($options);
        static::assertArrayHasKey('nonce_str', $resolved);
        static::assertSame('test_out_trade_no', $resolved['out_trade_no']);
        static::assertSame('test_transaction_id', $resolved['transaction_id']);
        static::assertSame('test_out_refund_no', $resolved['out_refund_no']);
        static::assertSame(12, $resolved['total_fee']);
        static::assertSame(10, $resolved['refund_fee']);
        static::assertSame([
            'nonce_str',
            'transaction_id',
            'out_trade_no',
            'refund_fee_type',
            'refund_desc',
            'refund_account',
            'notify_url',
            'out_refund_no',
            'total_fee',
            'refund_fee',
        ], $refund->getResolver()->getDefinedOptions());
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

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $refund = static::createRequest();
        $refund->setHttpClient($httpClient);

        $parsedResponse = $refund->send($options);
        static::assertSame($responseData, $parsedResponse);
    }

    public function testConfigureRequest(): void
    {
        $options = [
            'transaction_id' => 'test_transaction_id',
            'out_trade_no' => 'test_out_trade_no',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        $refund = static::createRequest();
        $request = new RequestOptions();

        $configureRequestRef = new \ReflectionMethod($refund, 'configureRequest');
        $configureRequestRef->setAccessible(true);
        $configureRequestRef->invoke($refund, $request, $refund->resolve($options));

        static::assertSame('POST', $request->getMethod());
        static::assertSame(Refund::URL, $request->getUrl());

        $requestOptions = $request->toArray();

        /**
         * @var array{
         *  nonce_str: string,
         *  sign: string,
         *  appid: string,
         *  mch_id: string,
         *  sign_type: string,
         *  transaction_id: string
         * }
         */
        $body = SerializerUtils::xmlDecode($requestOptions['body']);

        static::assertArrayHasKey('nonce_str', $body);
        static::assertArrayHasKey('sign', $body);
        static::assertSame('test_appid', $body['appid']);
        static::assertSame('test_mchid', $body['mch_id']);
        static::assertSame('HMAC-SHA256', $body['sign_type']);
        static::assertSame('test_transaction_id', $body['transaction_id']);

        $customOptions = [
            'out_trade_no' => 'test_out_trade_no',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
            'refund_fee_type' => 'CNY',
            'refund_desc' => 'test_refund_desc',
            'refund_account' => 'REFUND_SOURCE_UNSETTLED_FUNDS',
            'notify_url' => 'test_notify_url',
        ];

        $configureRequestRef->invoke($refund, $request, $refund->resolve($customOptions));
        $requestOptions = $request->toArray();

        /**
         * @var array{
         *  nonce_str: string,
         *  sign: string,
         *  appid: string,
         *  mch_id: string,
         *  sign_type: string,
         *  out_trade_no: string,
         *  refund_fee_type: string,
         *  refund_desc: string,
         *  refund_account: string,
         *  notify_url: string
         * }
         */
        $body = SerializerUtils::xmlDecode($requestOptions['body']);
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

        $refund = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($refund, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($refund, $response);
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

        $refund = static::createRequest();
        $parseResponseRef = new \ReflectionMethod($refund, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($refund, $response);
    }

    public function testMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required options "out_refund_no", "refund_fee", "total_fee" are missing');

        $refund = static::createRequest();
        $refund->resolve();
    }

    public function testNumberMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "transaction_id" or "out_trade_no" is missing');

        $unifiedorder = static::createRequest();
        $unifiedorder->resolve([
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ]);
    }

    public function testRefundFeeTypeInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "refund_fee_type" with value "foo" is invalid. Accepted values are: null, "CNY"');

        $unifiedorder = static::createRequest();
        $unifiedorder->resolve([
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

        $unifiedorder = static::createRequest();
        $unifiedorder->resolve([
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

        $refund = static::createRequest($configuration);
        $refund->send([
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

        $refund = static::createRequest($configuration);
        $refund->send([
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
