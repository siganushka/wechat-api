<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Payment\ConfigUtils;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationOptionsTest;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigUtilsTest extends TestCase
{
    private ?ConfigUtils $configUtils = null;

    protected function setUp(): void
    {
        $this->configUtils = ConfigUtils::create();
    }

    protected function tearDown(): void
    {
        $this->configUtils = null;
    }

    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->configUtils->configure($resolver);

        static::assertSame([
            'appid',
            'mchkey',
            'sign_type',
            'timestamp',
            'nonce_str',
            'prepay_id',
        ], $resolver->getDefinedOptions());

        $resolved = $resolver->resolve(['appid' => 'foo', 'mchkey' => 'bar', 'prepay_id' => 'baz']);
        static::assertSame('foo', $resolved['appid']);
        static::assertSame('bar', $resolved['mchkey']);
        static::assertSame('MD5', $resolved['sign_type']);
        static::assertArrayHasKey('timestamp', $resolved);
        static::assertArrayHasKey('nonce_str', $resolved);
        static::assertSame('baz', $resolved['prepay_id']);

        $resolved = $resolver->resolve([
            'appid' => 'foo',
            'mchkey' => 'bar',
            'sign_type' => 'HMAC-SHA256',
            'timestamp' => 'test_timestamp',
            'nonce_str' => 'test_nonce_str',
            'prepay_id' => 'baz',
        ]);

        static::assertSame('foo', $resolved['appid']);
        static::assertSame('bar', $resolved['mchkey']);
        static::assertSame('HMAC-SHA256', $resolved['sign_type']);
        static::assertSame('test_timestamp', $resolved['timestamp']);
        static::assertSame('test_nonce_str', $resolved['nonce_str']);
        static::assertSame('baz', $resolved['prepay_id']);
    }

    public function testGenerate(): void
    {
        $this->configUtils->extend(ConfigurationOptionsTest::create());

        $payConfig = $this->configUtils->generate('baz');
        static::assertSame('test_appid', $payConfig['appId']);
        static::assertSame('MD5', $payConfig['signType']);
        static::assertArrayHasKey('timeStamp', $payConfig);
        static::assertArrayHasKey('nonceStr', $payConfig);
        static::assertSame('prepay_id=baz', $payConfig['package']);
        static::assertArrayHasKey('paySign', $payConfig);
    }

    public function testGenerateFromOptions(): void
    {
        $payConfig = $this->configUtils->generateFromOptions(['appid' => 'foo', 'mchkey' => 'bar', 'prepay_id' => 'baz']);
        static::assertSame('foo', $payConfig['appId']);
        static::assertSame('MD5', $payConfig['signType']);
        static::assertArrayHasKey('timeStamp', $payConfig);
        static::assertArrayHasKey('nonceStr', $payConfig);
        static::assertSame('prepay_id=baz', $payConfig['package']);
        static::assertArrayHasKey('paySign', $payConfig);

        $paySign = $payConfig['paySign'];
        unset($payConfig['paySign']);

        $signatureUtils = SignatureUtils::create();
        static::assertTrue($signatureUtils->checkFromOptions($paySign, [
            'mchkey' => 'bar',
            'data' => $payConfig,
        ]));
    }
}
