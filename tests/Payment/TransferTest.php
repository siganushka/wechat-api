<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Test\RequestTestCase;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;
use Siganushka\ApiClient\Wechat\Payment\Transfer;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationManagerTest;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

class TransferTest extends RequestTestCase
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

        $configurationManager = ConfigurationManagerTest::create();
        $defaultConfig = $configurationManager->get('default');

        $options = [
            'appid' => $defaultConfig['appid'],
            'nonce_str' => uniqid(),
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ];

        static::assertSame([
            'mchid' => null,
            'mchkey' => null,
            'mch_client_cert' => null,
            'mch_client_key' => null,
            'sign_type' => 'MD5',
            'nonce_str' => $options['nonce_str'],
            'client_ip' => '0.0.0.0',
            'device_info' => null,
            'check_name' => 'NO_CHECK',
            're_user_name' => null,
            'scene' => null,
            'brand_id' => null,
            'finder_template_id' => null,
            'appid' => $options['appid'],
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ], $resolver->resolve($options));

        static::assertSame([
            'mchid' => $defaultConfig['mchid'],
            'mchkey' => $defaultConfig['mchkey'],
            'mch_client_cert' => $defaultConfig['mch_client_cert'],
            'mch_client_key' => $defaultConfig['mch_client_key'],
            'sign_type' => 'MD5',
            'nonce_str' => $options['nonce_str'],
            'client_ip' => '0.0.0.0',
            'device_info' => 'test_device_info',
            'check_name' => 'NO_CHECK',
            're_user_name' => 'test_re_user_name',
            'scene' => 'test_scene',
            'brand_id' => 16,
            'finder_template_id' => 'test_finder_template_id',
            'appid' => $options['appid'],
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ], $resolver->resolve($options + [
            'mchid' => $defaultConfig['mchid'],
            'mchkey' => $defaultConfig['mchkey'],
            'mch_client_cert' => $defaultConfig['mch_client_cert'],
            'mch_client_key' => $defaultConfig['mch_client_key'],
            'device_info' => 'test_device_info',
            're_user_name' => 'test_re_user_name',
            'scene' => 'test_scene',
            'brand_id' => 16,
            'finder_template_id' => 'test_finder_template_id',
        ]));
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

        $body = $this->decodeXML($requestOptions->toArray()['body']);

        $signature = $body['sign'];
        unset($body['sign']);

        $signatureUtils = SignatureUtils::create();
        static::assertSame($signature, $signatureUtils->generateFromOptions([
            'mchkey' => $options['mchkey'],
            'parameters' => $body,
        ]));

        static::assertSame([
            'mch_appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'nonce_str' => $options['nonce_str'],
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

        $body = $this->decodeXML($requestOptions->toArray()['body']);

        $signature = $body['sign'];
        unset($body['sign']);

        static::assertSame($signature, $signatureUtils->generateFromOptions([
            'mchkey' => $options['mchkey'],
            'sign_type' => 'HMAC-SHA256',
            'parameters' => $body,
        ]));

        static::assertSame([
            'mch_appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'device_info' => 'test_device_info',
            'nonce_str' => $options['nonce_str'],
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
        $configurationManager = ConfigurationManagerTest::create();
        $defaultConfig = $configurationManager->get('default');

        $options = [
            'appid' => $defaultConfig['appid'],
            'mchid' => $defaultConfig['mchid'],
            'mchkey' => $defaultConfig['mchkey'],
            'mch_client_cert' => $defaultConfig['mch_client_cert'],
            'mch_client_key' => $defaultConfig['mch_client_key'],
            'nonce_str' => uniqid(),
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
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
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ]);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build([
            'appid' => 123,
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ]);
    }

    public function testMchidNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "mchid" option');

        $this->request->build([
            'appid' => 'foo',
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

        $this->request->build([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'partner_trade_no' => 'test_partner_trade_no',
            'openid' => 'test_openid',
            'amount' => 1,
            'desc' => 'test_desc',
        ]);
    }

    protected function createRequest(): Transfer
    {
        $serializer = new Serializer([], [new XmlEncoder(), new JsonEncoder()]);

        return new Transfer($serializer);
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
