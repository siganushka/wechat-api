<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Payment\Query;
use Siganushka\ApiClient\Wechat\SerializerUtils;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;

class QueryTest extends TestCase
{
    public function testResolve(): void
    {
        $options = [
            'transaction_id' => 'test_transaction_id',
            'out_trade_no' => 'test_out_trade_no',
        ];

        $request = static::createRequest();

        $resolved = $request->resolve($options);
        static::assertArrayHasKey('nonce_str', $resolved);
        static::assertSame('test_out_trade_no', $resolved['out_trade_no']);
        static::assertSame('test_transaction_id', $resolved['transaction_id']);
        static::assertFalse($resolved['using_slave_api']);
    }

    public function testBuild(): void
    {
        $options = [
            'transaction_id' => 'test_transaction_id',
            'out_trade_no' => 'test_out_trade_no',
        ];

        $request = static::createRequest();
        $requestOptions = $request->build($options);

        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Query::URL, $requestOptions->getUrl());

        $body = SerializerUtils::xmlDecode($requestOptions->toArray()['body']);
        static::assertArrayHasKey('nonce_str', $body);
        static::assertArrayHasKey('sign', $body);
        static::assertSame('test_appid', $body['appid']);
        static::assertSame('test_mchid', $body['mch_id']);
        static::assertSame('HMAC-SHA256', $body['sign_type']);
        static::assertSame('test_transaction_id', $body['transaction_id']);

        $requestOptions = $request->build(['out_trade_no' => 'test_out_trade_no', 'using_slave_api' => true]);
        static::assertSame(Query::URL2, $requestOptions->getUrl());

        $body = SerializerUtils::xmlDecode($requestOptions->toArray()['body']);
        static::assertArrayHasKey('nonce_str', $body);
        static::assertArrayHasKey('sign', $body);
        static::assertSame('test_appid', $body['appid']);
        static::assertSame('test_mchid', $body['mch_id']);
        static::assertSame('HMAC-SHA256', $body['sign_type']);
        static::assertSame('test_out_trade_no', $body['out_trade_no']);
    }

    public function testSend(): void
    {
        $options = [
            'transaction_id' => 'test_transaction_id',
            'out_trade_no' => 'test_out_trade_no',
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
        $this->expectExceptionMessage('The required option "transaction_id" or "out_trade_no" is missing');

        $request = static::createRequest();
        $request->resolve();
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
            'transaction_id' => 'test_transaction_id',
            'out_trade_no' => 'test_out_trade_no',
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
            'transaction_id' => 'test_transaction_id',
            'out_trade_no' => 'test_out_trade_no',
        ]);
    }

    public static function createRequest(Configuration $configuration = null): Query
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::createConfiguration();
        }

        return new Query($configuration);
    }
}
