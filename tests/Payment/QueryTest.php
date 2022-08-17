<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Response\ResponseFactory;
use Siganushka\ApiClient\Test\RequestTestCase;
use Siganushka\ApiClient\Wechat\Payment\Query;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

class QueryTest extends RequestTestCase
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
            'using_slave_url',
            'transaction_id',
            'out_trade_no',
        ], $resolver->getDefinedOptions());

        $options = [
            'appid' => 'foo',
            'transaction_id' => 'test_transaction_id',
        ];

        $resolved = $resolver->resolve($options);
        static::assertSame('foo', $resolved['appid']);
        static::assertNull($resolved['mchid']);
        static::assertNull($resolved['mchkey']);
        static::assertSame('MD5', $resolved['sign_type']);
        static::assertArrayHasKey('nonce_str', $resolved);
        static::assertFalse($resolved['using_slave_url']);
        static::assertSame('test_transaction_id', $resolved['transaction_id']);
        static::assertNull($resolved['out_trade_no']);

        $resolved = $resolver->resolve([
            'appid' => 'foo',
            'mchid' => 'bar',
            'mchkey' => 'test_mchkey',
            'sign_type' => 'HMAC-SHA256',
            'nonce_str' => 'test_nonce_str',
            'using_slave_url' => true,
            'transaction_id' => 'test_transaction_id',
            'out_trade_no' => 'test_out_trade_no',
        ]);

        static::assertSame('foo', $resolved['appid']);
        static::assertSame('bar', $resolved['mchid']);
        static::assertSame('test_mchkey', $resolved['mchkey']);
        static::assertSame('HMAC-SHA256', $resolved['sign_type']);
        static::assertSame('test_nonce_str', $resolved['nonce_str']);
        static::assertTrue($resolved['using_slave_url']);
        static::assertSame('test_transaction_id', $resolved['transaction_id']);
        static::assertSame('test_out_trade_no', $resolved['out_trade_no']);
    }

    public function testBuild(): void
    {
        $options = [
            'appid' => 'foo',
            'mchid' => 'bar',
            'mchkey' => 'test_mchkey',
            'nonce_str' => uniqid(),
            'transaction_id' => 'test_transaction_id',
        ];

        $requestOptions = $this->request->build($options);
        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Query::URL, $requestOptions->getUrl());

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
            'transaction_id' => $options['transaction_id'],
            'nonce_str' => $options['nonce_str'],
            'sign_type' => 'MD5',
        ], $body);

        $requestOptions = $this->request->build($options + [
            'sign_type' => 'HMAC-SHA256',
            'out_trade_no' => 'test_out_trade_no',
            'using_slave_url' => true,
        ]);

        static::assertSame(Query::URL2, $requestOptions->getUrl());

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
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => 'test_out_trade_no',
            'nonce_str' => $options['nonce_str'],
            'sign_type' => 'HMAC-SHA256',
        ], $body);
    }

    public function testSend(): void
    {
        $options = [
            'appid' => 'foo',
            'mchid' => 'bar',
            'mchkey' => 'test_mchkey',
            'transaction_id' => 'test_transaction_id',
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
            'transaction_id' => 'test_transaction_id',
        ]);
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        $this->request->build([
            'appid' => 123,
            'transaction_id' => 'test_transaction_id',
        ]);
    }

    public function testMchidNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "mchid" option');

        $this->request->build([
            'appid' => 'foo',
            'transaction_id' => 'test_transaction_id',
        ]);
    }

    public function testMchkeyNoConfigurationException(): void
    {
        $this->expectException(NoConfigurationException::class);
        $this->expectExceptionMessage('No configured value for "mchkey" option');

        $this->request->build([
            'appid' => 'foo',
            'mchid' => 'bar',
            'transaction_id' => 'test_transaction_id',
        ]);
    }

    protected function createRequest(): Query
    {
        $serializer = new Serializer([], [new XmlEncoder(), new JsonEncoder()]);

        return new Query($serializer);
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
