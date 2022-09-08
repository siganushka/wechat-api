<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;
use Siganushka\ApiClient\Wechat\Payment\Transfer;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

class TransferTest extends TestCase
{
    protected ?Transfer $request = null;

    protected function setUp(): void
    {
        $this->request = new Transfer();
    }

    protected function tearDown(): void
    {
        $this->request = null;
    }

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
            'noncestr',
            'client_ip',
            'device_info',
            'partner_trade_no',
            'openid',
            'check_name',
            're_user_name',
            'amount',
            'desc',
            'scene',
            'brand_id',
            'finder_template_id',
        ], $resolver->getDefinedOptions());

        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
            'noncestr' => 'test_noncestr',
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ];

        static::assertSame([
            'sign_type' => 'MD5',
            'noncestr' => $options['noncestr'],
            'client_ip' => '0.0.0.0',
            'device_info' => null,
            'check_name' => 'NO_CHECK',
            're_user_name' => null,
            'scene' => null,
            'brand_id' => null,
            'finder_template_id' => null,
            'appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'mchkey' => $options['mchkey'],
            'mch_client_cert' => $options['mch_client_cert'],
            'mch_client_key' => $options['mch_client_key'],
            'partner_trade_no' => $options['partner_trade_no'],
            'openid' => $options['openid'],
            'amount' => $options['amount'],
            'desc' => $options['desc'],
        ], $resolver->resolve($options));

        static::assertSame([
            'sign_type' => 'HMAC-SHA256',
            'noncestr' => $options['noncestr'],
            'client_ip' => '127.0.0.1',
            'device_info' => 'test_device_info',
            'check_name' => 'FORCE_CHECK',
            're_user_name' => 'test_re_user_name',
            'scene' => 'test_scene',
            'brand_id' => 16,
            'finder_template_id' => 'test_finder_template_id',
            'appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'mchkey' => $options['mchkey'],
            'mch_client_cert' => $options['mch_client_cert'],
            'mch_client_key' => $options['mch_client_key'],
            'partner_trade_no' => $options['partner_trade_no'],
            'openid' => $options['openid'],
            'amount' => $options['amount'],
            'desc' => $options['desc'],
        ], $resolver->resolve($options + [
            'sign_type' => 'HMAC-SHA256',
            'client_ip' => '127.0.0.1',
            'device_info' => 'test_device_info',
            'check_name' => 'FORCE_CHECK',
            're_user_name' => 'test_re_user_name',
            'scene' => 'test_scene',
            'brand_id' => 16,
            'finder_template_id' => 'test_finder_template_id',
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
            'noncestr' => uniqid(),
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ];

        $requestOptions = $this->request->build($options);
        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Transfer::URL, $requestOptions->getUrl());
        static::assertSame($requestOptions->toArray()['local_cert'], $options['mch_client_cert']);
        static::assertSame($requestOptions->toArray()['local_pk'], $options['mch_client_key']);

        $body = $this->createSerializer()->decode($requestOptions->toArray()['body'], 'xml');

        $signature = $body['sign'];
        unset($body['sign']);

        $signatureUtils = SignatureUtils::create();
        static::assertSame($signature, $signatureUtils->generate([
            'mchkey' => $options['mchkey'],
            'data' => $body,
        ]));

        static::assertSame([
            'mch_appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'nonce_str' => $options['noncestr'],
            'partner_trade_no' => $options['partner_trade_no'],
            'openid' => $options['openid'],
            'check_name' => 'NO_CHECK',
            'amount' => (string) $options['amount'],
            'desc' => $options['desc'],
            'spbill_create_ip' => '0.0.0.0',
        ], $body);

        $requestOptions = $this->request->build($options + [
            'sign_type' => 'HMAC-SHA256',
            'device_info' => 'test_device_info',
            're_user_name' => 'test_re_user_name',
            'scene' => 'test_scene',
            'brand_id' => 16,
            'finder_template_id' => 'test_finder_template_id',
        ]);

        $body = $this->createSerializer()->decode($requestOptions->toArray()['body'], 'xml');

        $signature = $body['sign'];
        unset($body['sign']);

        static::assertSame($signature, $signatureUtils->generate([
            'mchkey' => $options['mchkey'],
            'sign_type' => 'HMAC-SHA256',
            'data' => $body,
        ]));

        static::assertSame([
            'mch_appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'device_info' => 'test_device_info',
            'nonce_str' => $options['noncestr'],
            'partner_trade_no' => $options['partner_trade_no'],
            'openid' => $options['openid'],
            'check_name' => 'NO_CHECK',
            're_user_name' => 'test_re_user_name',
            'amount' => (string) $options['amount'],
            'desc' => $options['desc'],
            'spbill_create_ip' => '0.0.0.0',
            'scene' => 'test_scene',
            'brand_id' => '16',
            'finder_template_id' => 'test_finder_template_id',
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
            'noncestr' => uniqid(),
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ];

        $data = [
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
        ];

        $body = $this->createSerializer()->encode($data, 'xml');

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $result = (new Transfer($client))->send($options);
        static::assertSame($data, $result);
    }

    public function testSendWithParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test_return_msg');

        $configuration = ConfigurationTest::create();

        $options = [
            'appid' => $configuration['appid'],
            'mchid' => $configuration['mchid'],
            'mchkey' => $configuration['mchkey'],
            'mch_client_cert' => $configuration['mch_client_cert'],
            'mch_client_key' => $configuration['mch_client_key'],
            'noncestr' => uniqid(),
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ];

        $data = [
            'return_code' => 'FAIL',
            'return_msg' => 'test_return_msg',
        ];

        $body = $this->createSerializer()->encode($data, 'xml');

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        (new Transfer($client))->send($options);
    }

    public function testResultCodeParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test_err_code_des');

        $configuration = ConfigurationTest::create();

        $options = [
            'appid' => $configuration['appid'],
            'mchid' => $configuration['mchid'],
            'mchkey' => $configuration['mchkey'],
            'mch_client_cert' => $configuration['mch_client_cert'],
            'mch_client_key' => $configuration['mch_client_key'],
            'noncestr' => uniqid(),
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ];

        $data = [
            'result_code' => 'FAIL',
            'err_code_des' => 'test_err_code_des',
        ];

        $body = $this->createSerializer()->encode($data, 'xml');

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        (new Transfer($client))->send($options);
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
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
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
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
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
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ]);
    }

    private function createSerializer(): Serializer
    {
        return new Serializer([], [new XmlEncoder(), new JsonEncoder()]);
    }
}
