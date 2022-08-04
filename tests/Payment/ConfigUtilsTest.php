<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Payment\ConfigUtils;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;

class ConfigUtilsTest extends TestCase
{
    public function testAll(): void
    {
        $options = [
            'appid' => 'test_appid',
            'secret' => 'test_secret',
            'mchkey' => 'test_mchkey',
        ];

        $configuration = new Configuration($options);
        $configUtils = new ConfigUtils($configuration);

        $payConfig = $configUtils->generate('test_prepay_id');
        static::assertArrayHasKey('appId', $payConfig);
        static::assertArrayHasKey('signType', $payConfig);
        static::assertArrayHasKey('timeStamp', $payConfig);
        static::assertArrayHasKey('nonceStr', $payConfig);
        static::assertArrayHasKey('package', $payConfig);
        static::assertArrayHasKey('paySign', $payConfig);
        static::assertSame($configuration['appid'], $payConfig['appId']);
        static::assertSame($configuration['sign_type'], $payConfig['signType']);
        static::assertSame('prepay_id=test_prepay_id', $payConfig['package']);

        $signatureUtils = new SignatureUtils($configuration);
        static::assertTrue($signatureUtils->checkParameters($payConfig, 'paySign'));

        $sign = $payConfig['paySign'];
        unset($payConfig['paySign']);

        static::assertTrue($signatureUtils->check($payConfig, $sign));
    }
}
