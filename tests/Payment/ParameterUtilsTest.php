<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Payment\ParameterUtils;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParameterUtilsTest extends TestCase
{
    private ?ParameterUtils $parameterUtils = null;

    protected function setUp(): void
    {
        $this->parameterUtils = ParameterUtils::create();
    }

    protected function tearDown(): void
    {
        $this->parameterUtils = null;
    }

    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->parameterUtils->configure($resolver);

        static::assertSame([
            'appid',
            'mchid',
            'mchkey',
            'sign_type',
            'timestamp',
            'noncestr',
            'prepay_id',
        ], $resolver->getDefinedOptions());

        $resolved = $resolver->resolve(['appid' => 'test_appid', 'mchid' => 'test_mchid', 'mchkey' => 'test_mchkey', 'prepay_id' => 'test_prepay_id']);
        static::assertSame('test_appid', $resolved['appid']);
        static::assertSame('test_mchid', $resolved['mchid']);
        static::assertSame('test_mchkey', $resolved['mchkey']);
        static::assertSame('MD5', $resolved['sign_type']);
        static::assertArrayHasKey('timestamp', $resolved);
        static::assertArrayHasKey('noncestr', $resolved);
        static::assertSame('test_prepay_id', $resolved['prepay_id']);

        $resolved = $resolver->resolve([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'sign_type' => 'HMAC-SHA256',
            'timestamp' => 'test_timestamp',
            'noncestr' => 'test_noncestr',
            'prepay_id' => 'test_prepay_id',
        ]);

        static::assertSame('test_appid', $resolved['appid']);
        static::assertSame('test_mchid', $resolved['mchid']);
        static::assertSame('test_mchkey', $resolved['mchkey']);
        static::assertSame('HMAC-SHA256', $resolved['sign_type']);
        static::assertSame('test_timestamp', $resolved['timestamp']);
        static::assertSame('test_noncestr', $resolved['noncestr']);
        static::assertSame('test_prepay_id', $resolved['prepay_id']);
    }

    public function testGenerateJsapi(): void
    {
        $parameter = $this->parameterUtils->jsapi(['appid' => 'test_appid', 'mchid' => 'test_mchid', 'mchkey' => 'test_mchkey', 'prepay_id' => 'test_prepay_id']);
        static::assertSame('test_appid', $parameter['appId']);
        static::assertSame('prepay_id=test_prepay_id', $parameter['package']);
        static::assertSame('MD5', $parameter['signType']);
        static::assertArrayHasKey('timeStamp', $parameter);
        static::assertArrayHasKey('nonceStr', $parameter);
        static::assertArrayHasKey('paySign', $parameter);

        $sign = $parameter['paySign'];
        unset($parameter['paySign']);

        $signatureUtils = SignatureUtils::create();
        static::assertTrue($signatureUtils->check($sign, [
            'mchkey' => 'test_mchkey',
            'data' => $parameter,
        ]));
    }

    public function testGenerateApp(): void
    {
        $parameter = $this->parameterUtils->app(['appid' => 'test_appid', 'mchid' => 'test_mchid', 'mchkey' => 'test_mchkey', 'prepay_id' => 'test_prepay_id']);
        static::assertSame('test_appid', $parameter['appid']);
        static::assertSame('test_mchid', $parameter['partnerid']);
        static::assertSame('test_prepay_id', $parameter['prepayid']);
        static::assertSame('Sign=WXPay', $parameter['package']);
        static::assertArrayHasKey('noncestr', $parameter);
        static::assertArrayHasKey('timestamp', $parameter);
        static::assertArrayHasKey('sign', $parameter);

        $sign = $parameter['sign'];
        unset($parameter['sign']);

        $signatureUtils = SignatureUtils::create();
        static::assertTrue($signatureUtils->check($sign, [
            'mchkey' => 'test_mchkey',
            'data' => $parameter,
        ]));
    }
}
