<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Test\RequestTestCase;
use Siganushka\ApiClient\Wechat\Payment\Refund;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationManagerTest;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
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

        $configurationManager = ConfigurationManagerTest::create();
        $defaultConfig = $configurationManager->get('default');

        $options = [
            'appid' => $defaultConfig['appid'],
            'nonce_str' => 'test_nonce_str',
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        static::assertSame([
            'mchid' => null,
            'mchkey' => null,
            'mch_client_cert' => null,
            'mch_client_key' => null,
            'sign_type' => 'MD5',
            'nonce_str' => $options['nonce_str'],
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => null,
            'refund_fee_type' => null,
            'refund_desc' => null,
            'refund_account' => null,
            'notify_url' => null,
            'appid' => $options['appid'],
            'out_refund_no' => $options['out_refund_no'],
            'total_fee' => $options['total_fee'],
            'refund_fee' => $options['refund_fee'],
        ], $resolver->resolve($options));

        $options = array_merge($options, [
            'mchid' => $defaultConfig['mchid'],
            'mchkey' => $defaultConfig['mchkey'],
            'mch_client_cert' => $defaultConfig['mch_client_cert'],
            'mch_client_key' => $defaultConfig['mch_client_key'],
            'sign_type' => 'HMAC-SHA256',
            'nonce_str' => 'test_nonce_str',
            'out_trade_no' => 'test_out_trade_no',
            'refund_fee_type' => 'CNY',
            'refund_desc' => 'test_refund_desc',
            'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
            'notify_url' => 'test_notify_url',
        ]);

        static::assertSame([
            'mchid' => $options['mchid'],
            'mchkey' => $options['mchkey'],
            'mch_client_cert' => $options['mch_client_cert'],
            'mch_client_key' => $options['mch_client_key'],
            'sign_type' => $options['sign_type'],
            'nonce_str' => $options['nonce_str'],
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => $options['out_trade_no'],
            'refund_fee_type' => $options['refund_fee_type'],
            'refund_desc' => $options['refund_desc'],
            'refund_account' => $options['refund_account'],
            'notify_url' => $options['notify_url'],
            'appid' => $options['appid'],
            'out_refund_no' => $options['out_refund_no'],
            'total_fee' => $options['total_fee'],
            'refund_fee' => $options['refund_fee'],
        ], $resolver->resolve($options));
    }

    public function testBuild(): void
    {
        $configurationManager = ConfigurationManagerTest::create();
        $defaultConfig = $configurationManager->get('default');

        $options = [
            'appid' => $defaultConfig['appid'],
            'mchid' => $defaultConfig['mchid'],
            'mchkey' => $defaultConfig['mchkey'],
            'mch_client_cert' => $defaultConfig['mch_client_cert'],
            'mch_client_key' => $defaultConfig['mch_client_key'],
            'nonce_str' => uniqid(),
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        $requestOptions = $this->request->build($options);
        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Refund::URL, $requestOptions->getUrl());
        static::assertSame($requestOptions->toArray()['local_cert'], $defaultConfig['mch_client_cert']);
        static::assertSame($requestOptions->toArray()['local_pk'], $defaultConfig['mch_client_key']);

        $body = $this->decodeXML($requestOptions->toArray()['body']);

        $signature = $body['sign'];
        unset($body['sign']);

        $signatureUtils = SignatureUtils::create();
        static::assertSame($signature, $signatureUtils->generateFromOptions([
            'mchkey' => $options['mchkey'],
            'parameters' => $body,
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
            'parameters' => $body,
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
        $configurationManager = ConfigurationManagerTest::create();
        $defaultConfig = $configurationManager->get('default');

        $options = [
            'appid' => $defaultConfig['appid'],
            'mchid' => $defaultConfig['mchid'],
            'mchkey' => $defaultConfig['mchkey'],
            'mch_client_cert' => $defaultConfig['mch_client_cert'],
            'mch_client_key' => $defaultConfig['mch_client_key'],
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

        $result = $this->request->send($client, $options);
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

        $configurationManager = ConfigurationManagerTest::create();
        $defaultConfig = $configurationManager->get('default');

        $this->request->build([
            'mchid' => $defaultConfig['mchid'],
            'mchkey' => $defaultConfig['mchkey'],
            'mch_client_cert' => $defaultConfig['mch_client_cert'],
            'mch_client_key' => $defaultConfig['mch_client_key'],
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ]);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        $configurationManager = ConfigurationManagerTest::create();
        $defaultConfig = $configurationManager->get('default');

        $this->request->build([
            'appid' => 123,
            'mchid' => $defaultConfig['mchid'],
            'mchkey' => $defaultConfig['mchkey'],
            'mch_client_cert' => $defaultConfig['mch_client_cert'],
            'mch_client_key' => $defaultConfig['mch_client_key'],
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ]);
    }

    public function testMchidNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "mchid" option');

        $configurationManager = ConfigurationManagerTest::create();
        $defaultConfig = $configurationManager->get('default');

        $this->request->build([
            'appid' => $defaultConfig['appid'],
            'mchkey' => $defaultConfig['mchkey'],
            'mch_client_cert' => $defaultConfig['mch_client_cert'],
            'mch_client_key' => $defaultConfig['mch_client_key'],
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ]);
    }

    public function testMchkeyNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "mchkey" option');

        $configurationManager = ConfigurationManagerTest::create();
        $defaultConfig = $configurationManager->get('default');

        $this->request->build([
            'appid' => $defaultConfig['appid'],
            'mchid' => $defaultConfig['mchid'],
            'mch_client_cert' => $defaultConfig['mch_client_cert'],
            'mch_client_key' => $defaultConfig['mch_client_key'],
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
