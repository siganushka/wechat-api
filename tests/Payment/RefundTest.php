<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Test\RequestTestCase;
use Siganushka\ApiClient\Wechat\Payment\Refund;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

class RefundTest extends RequestTestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertSame([
            'appid',
            'mchid',
            'mchkey',
            'mch_client_cert',
            'mch_client_key',
            'sign_type',
            'nonce_str',
            'transaction_id',
            'out_trade_no',
            'out_refund_no',
            'total_fee',
            'refund_fee',
            'refund_fee_type',
            'refund_desc',
            'refund_account',
            'notify_url',
        ], $resolver->getDefinedOptions());

        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
            'nonce_str' => 'test_nonce_str',
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        static::assertSame([
            'sign_type' => 'MD5',
            'nonce_str' => $options['nonce_str'],
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => null,
            'refund_fee_type' => null,
            'refund_desc' => null,
            'refund_account' => null,
            'notify_url' => null,
            'appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'mchkey' => $options['mchkey'],
            'mch_client_cert' => $options['mch_client_cert'],
            'mch_client_key' => $options['mch_client_key'],
            'out_refund_no' => $options['out_refund_no'],
            'total_fee' => $options['total_fee'],
            'refund_fee' => $options['refund_fee'],
        ], $resolver->resolve($options));

        static::assertSame([
            'sign_type' => 'HMAC-SHA256',
            'nonce_str' => $options['nonce_str'],
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => 'test_out_trade_no',
            'refund_fee_type' => 'CNY',
            'refund_desc' => 'test_refund_desc',
            'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
            'notify_url' => 'test_notify_url',
            'appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'mchkey' => $options['mchkey'],
            'mch_client_cert' => $options['mch_client_cert'],
            'mch_client_key' => $options['mch_client_key'],
            'out_refund_no' => $options['out_refund_no'],
            'total_fee' => $options['total_fee'],
            'refund_fee' => $options['refund_fee'],
        ], $resolver->resolve($options + [
            'sign_type' => 'HMAC-SHA256',
            'out_trade_no' => 'test_out_trade_no',
            'refund_fee_type' => 'CNY',
            'refund_desc' => 'test_refund_desc',
            'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
            'notify_url' => 'test_notify_url',
        ]));
    }

    public function testBuild(): void
    {
        $configuration = ConfigurationTest::create();

        $options = [
            'appid' => $configuration['appid'],
            'mchid' => $configuration['mchid'],
            'mchkey' => $configuration['mchkey'],
            'mch_client_cert' => $configuration['mch_client_cert'],
            'mch_client_key' => $configuration['mch_client_key'],
            'nonce_str' => uniqid(),
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        $requestOptions = $this->request->build($options);
        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Refund::URL, $requestOptions->getUrl());
        static::assertSame($requestOptions->toArray()['local_cert'], $configuration['mch_client_cert']);
        static::assertSame($requestOptions->toArray()['local_pk'], $configuration['mch_client_key']);

        $body = $this->decodeXML($requestOptions->toArray()['body']);

        $signature = $body['sign'];
        unset($body['sign']);

        $signatureUtils = SignatureUtils::create();
        static::assertSame($signature, $signatureUtils->generateFromOptions([
            'mchkey' => $options['mchkey'],
            'data' => $body,
        ]));

        static::assertSame([
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'sign_type' => 'MD5',
            'nonce_str' => $options['nonce_str'],
            'transaction_id' => $options['transaction_id'],
            'out_refund_no' => $options['out_refund_no'],
            'total_fee' => (string) $options['total_fee'],
            'refund_fee' => (string) $options['refund_fee'],
        ], $body);

        $requestOptions = $this->request->build($options + [
            'sign_type' => 'HMAC-SHA256',
            'out_trade_no' => 'test_out_trade_no',
            'refund_fee_type' => 'CNY',
            'refund_desc' => 'test_refund_desc',
            'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
            'notify_url' => 'test_notify_url',
        ]);

        $body = $this->decodeXML($requestOptions->toArray()['body']);

        $signature = $body['sign'];
        unset($body['sign']);

        static::assertSame($signature, $signatureUtils->generateFromOptions([
            'mchkey' => $options['mchkey'],
            'sign_type' => 'HMAC-SHA256',
            'data' => $body,
        ]));

        static::assertSame([
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'sign_type' => 'HMAC-SHA256',
            'nonce_str' => $options['nonce_str'],
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => 'test_out_trade_no',
            'out_refund_no' => $options['out_refund_no'],
            'total_fee' => (string) $options['total_fee'],
            'refund_fee' => (string) $options['refund_fee'],
            'refund_fee_type' => 'CNY',
            'refund_desc' => 'test_refund_desc',
            'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
            'notify_url' => 'test_notify_url',
        ], $body);
    }

    public function testSend(): void
    {
        $configuration = ConfigurationTest::create();

        $options = [
            'appid' => $configuration['appid'],
            'mchid' => $configuration['mchid'],
            'mchkey' => $configuration['mchkey'],
            'mch_client_cert' => $configuration['mch_client_cert'],
            'mch_client_key' => $configuration['mch_client_key'],
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        $data = [
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
        ];

        $xml = $this->encodeXML($data);
        $response = ResponseFactory::createMockResponse($xml);
        $client = new MockHttpClient($response);

        $result = $this->request->setHttpClient($client)->send($options);
        static::assertSame($data, $result);
    }

    public function testParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test_return_msg');

        $data = [
            'return_code' => 'FAIL',
            'return_msg' => 'test_return_msg',
        ];

        $xml = $this->encodeXML($data);
        $response = ResponseFactory::createMockResponse($xml);

        $parseResponseRef = new \ReflectionMethod($this->request, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($this->request, $response);
    }

    public function testResultCodeParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test_err_code_des');

        $data = [
            'result_code' => 'FAIL',
            'err_code_des' => 'test_err_code_des',
        ];

        $xml = $this->encodeXML($data);
        $response = ResponseFactory::createMockResponse($xml);

        $parseResponseRef = new \ReflectionMethod($this->request, 'parseResponse');
        $parseResponseRef->setAccessible(true);
        $parseResponseRef->invoke($this->request, $response);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        $this->request->build([
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
            'nonce_str' => 'test_nonce_str',
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ]);
    }

    public function testMchidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "mchid" is missing');

        $this->request->build([
            'appid' => 'test_appid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
            'nonce_str' => 'test_nonce_str',
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ]);
    }

    public function testMchkeyMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "mchkey" is missing');

        $this->request->build([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
            'nonce_str' => 'test_nonce_str',
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ]);
    }

    protected function createRequest(): Refund
    {
        $serializer = new Serializer([], [new XmlEncoder(), new JsonEncoder()]);

        return new Refund($serializer);
    }

    protected function encodeXML(array $data): string
    {
        return (new XmlEncoder())->encode($data, 'xml');
    }

    protected function decodeXML(string $data): array
    {
        return (new XmlEncoder())->decode($data, 'xml');
    }
}
