<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Payment\ParameterUtils;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;

class ParameterUtilsTest extends TestCase
{
    public function testAll(): void
    {
        $options = [
            'appid' => 'test_appid',
            'appsecret' => 'test_appsecret',
            'mchkey' => 'test_mchkey',
        ];

        $configuration = new Configuration($options);
        $parameterUtils = new ParameterUtils($configuration);

        $jsapiParameters = $parameterUtils->jsapi('test_prepay_id');
        static::assertArrayHasKey('appId', $jsapiParameters);
        static::assertArrayHasKey('signType', $jsapiParameters);
        static::assertArrayHasKey('timeStamp', $jsapiParameters);
        static::assertArrayHasKey('nonceStr', $jsapiParameters);
        static::assertArrayHasKey('package', $jsapiParameters);
        static::assertArrayHasKey('sign', $jsapiParameters);
        static::assertSame($configuration['appid'], $jsapiParameters['appId']);
        static::assertSame($configuration['sign_type'], $jsapiParameters['signType']);
        static::assertSame('prepay_id=test_prepay_id', $jsapiParameters['package']);

        $signatureUtils = new SignatureUtils($configuration);
        static::assertTrue($signatureUtils->checkParameters($jsapiParameters));

        $sign = $jsapiParameters['sign'];
        unset($jsapiParameters['sign']);

        static::assertTrue($signatureUtils->check($jsapiParameters, $sign));
    }
}
