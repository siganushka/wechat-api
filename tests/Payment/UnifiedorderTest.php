<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Test\RequestTestCase;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;
use Siganushka\ApiClient\Wechat\Payment\Unifiedorder;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

class UnifiedorderTest extends RequestTestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertSame([
            'appid',
            'mchid',
            'mchkey',
            'sign_type',
            'nonce_str',
            'client_ip',
            'using_slave_url',
            'device_info',
            'body',
            'detail',
            'attach',
            'out_trade_no',
            'fee_type',
            'total_fee',
            'time_start',
            'time_expire',
            'goods_tag',
            'notify_url',
            'trade_type',
            'product_id',
            'limit_pay',
            'openid',
            'receipt',
            'profit_sharing',
            'scene_info',
        ], $resolver->getDefinedOptions());

        $options = [
            'appid' => 'test_appid',
            'nonce_str' => 'test_nonce_str',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ];

        static::assertEquals([
            'appid' => $options['appid'],
            'mchid' => null,
            'mchkey' => null,
            'sign_type' => 'MD5',
            'nonce_str' => $options['nonce_str'],
            'client_ip' => '0.0.0.0',
            'using_slave_url' => false,
            'device_info' => null,
            'body' => $options['body'],
            'detail' => null,
            'attach' => null,
            'out_trade_no' => $options['out_trade_no'],
            'fee_type' => null,
            'total_fee' => 1,
            'time_start' => null,
            'time_expire' => null,
            'goods_tag' => null,
            'notify_url' => $options['notify_url'],
            'trade_type' => $options['trade_type'],
            'product_id' => null,
            'limit_pay' => null,
            'openid' => $options['openid'],
            'receipt' => null,
            'profit_sharing' => null,
            'scene_info' => null,
        ], $resolver->resolve($options));

        $timeStartAt = new \DateTimeImmutable();
        $timeExpireAt = $timeStartAt->modify('+7 days');
        $options = array_merge($options, [
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'sign_type' => 'HMAC-SHA256',
            'client_ip' => '127.0.0.1',
            'using_slave_url' => true,
            'device_info' => 'test_device_info',
            'detail' => 'test_detail',
            'attach' => 'test_attach',
            'fee_type' => 'CNY',
            'time_start' => $timeStartAt,
            'time_expire' => $timeExpireAt,
            'goods_tag' => 'test_goods_tag',
            'product_id' => 'test_product_id',
            'limit_pay' => 'no_credit',
            'receipt' => 'Y',
            'profit_sharing' => 'Y',
            'scene_info' => 'test_scene_info',
        ]);

        static::assertEquals([
            'appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'mchkey' => $options['mchkey'],
            'sign_type' => $options['sign_type'],
            'nonce_str' => $options['nonce_str'],
            'client_ip' => $options['client_ip'],
            'using_slave_url' => $options['using_slave_url'],
            'device_info' => $options['device_info'],
            'body' => $options['body'],
            'detail' => $options['detail'],
            'attach' => $options['attach'],
            'out_trade_no' => $options['out_trade_no'],
            'fee_type' => $options['fee_type'],
            'total_fee' => $options['total_fee'],
            'time_start' => $timeStartAt->format('YmdHis'),
            'time_expire' => $timeExpireAt->format('YmdHis'),
            'goods_tag' => $options['goods_tag'],
            'notify_url' => $options['notify_url'],
            'trade_type' => $options['trade_type'],
            'product_id' => $options['product_id'],
            'limit_pay' => $options['limit_pay'],
            'openid' => $options['openid'],
            'receipt' => $options['receipt'],
            'profit_sharing' => $options['profit_sharing'],
            'scene_info' => $options['scene_info'],
        ], $resolver->resolve($options));
    }

    public function testBuild(): void
    {
        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'nonce_str' => 'test_nonce_str',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ];

        $requestOptions = $this->request->build($options);
        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Unifiedorder::URL, $requestOptions->getUrl());

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
            'nonce_str' => $options['nonce_str'],
            'sign_type' => 'MD5',
            'body' => $options['body'],
            'out_trade_no' => $options['out_trade_no'],
            'total_fee' => (string) $options['total_fee'],
            'spbill_create_ip' => '0.0.0.0',
            'notify_url' => $options['notify_url'],
            'trade_type' => $options['trade_type'],
            'openid' => $options['openid'],
        ], $body);

        $timeStartAt = new \DateTimeImmutable();
        $timeExpireAt = $timeStartAt->modify('+7 days');
        $options = array_merge($options, [
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'sign_type' => 'HMAC-SHA256',
            'client_ip' => '127.0.0.1',
            'using_slave_url' => true,
            'device_info' => 'test_device_info',
            'detail' => 'test_detail',
            'attach' => 'test_attach',
            'fee_type' => 'CNY',
            'time_start' => $timeStartAt,
            'time_expire' => $timeExpireAt,
            'goods_tag' => 'test_goods_tag',
            'product_id' => 'test_product_id',
            'limit_pay' => 'no_credit',
            'receipt' => 'Y',
            'profit_sharing' => 'Y',
            'scene_info' => 'test_scene_info',
        ]);

        $requestOptions = $this->request->build($options);

        $body = $this->decodeXML($requestOptions->toArray()['body']);

        $signature = $body['sign'];
        unset($body['sign']);

        static::assertSame($signature, $signatureUtils->generateFromOptions([
            'mchkey' => $options['mchkey'],
            'sign_type' => $options['sign_type'],
            'parameters' => $body,
        ]));

        static::assertSame([
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'device_info' => $options['device_info'],
            'nonce_str' => $options['nonce_str'],
            'sign_type' => $options['sign_type'],
            'body' => $options['body'],
            'detail' => $options['detail'],
            'attach' => $options['attach'],
            'out_trade_no' => $options['out_trade_no'],
            'fee_type' => $options['fee_type'],
            'total_fee' => (string) $options['total_fee'],
            'spbill_create_ip' => $options['client_ip'],
            'time_start' => $timeStartAt->format('YmdHis'),
            'time_expire' => $timeExpireAt->format('YmdHis'),
            'goods_tag' => $options['goods_tag'],
            'notify_url' => $options['notify_url'],
            'trade_type' => $options['trade_type'],
            'product_id' => $options['product_id'],
            'limit_pay' => $options['limit_pay'],
            'openid' => $options['openid'],
            'receipt' => $options['receipt'],
            'profit_sharing' => $options['profit_sharing'],
            'scene_info' => $options['scene_info'],
        ], $body);
    }

    public function testSend(): void
    {
        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
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

        $this->request->build([
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ]);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build([
            'appid' => 123,
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ]);
    }

    public function testMchidNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "mchid" option');

        $this->request->build([
            'appid' => 'test_appid',
            'mchkey' => 'test_mchkey',
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

        $this->request->build([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ]);
    }

    protected function createRequest(): Unifiedorder
    {
        $serializer = new Serializer([], [new XmlEncoder(), new JsonEncoder()]);

        return new Unifiedorder($serializer);
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
