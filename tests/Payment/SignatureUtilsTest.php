<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;

class SignatureUtilsTest extends TestCase
{
    /**
     * @dataProvider getSignatureProvider
     *
     * @param array<string, mixed> $parameters
     */
    public function testAll(string $key, array $parameters, string $sign, string $signType = null): void
    {
        $configuration = new Configuration([
            'appid' => 'aaa',
            'appsecret' => 'bbb',
            'mchkey' => $key,
            'sign_type' => $signType,
        ]);

        $signatureUtils = new SignatureUtils($configuration);
        static::assertSame($sign, $signatureUtils->generate($parameters));
        static::assertTrue($signatureUtils->check($parameters, $sign));
    }

    /**
     * @return array<int, array<mixed>>
     */
    public function getSignatureProvider(): array
    {
        return [
            [
                'foo_key',
                ['foo' => 'hello'],
                'BC5C27603A4F305796AC0D42737C3AF4',
                'MD5',
            ],
            [
                'bar_key',
                ['bar' => 'world'],
                '225AFF17105D22B3548D13B875EEA92E783734367FF0C7BD68F67041BD7DCC00',
                'HMAC-SHA256',
            ],
            [
                'baz_key',
                ['bar' => 'hello world'],
                '3079E28A7DA4046A31CE4D106218A457',
                'MD5',
            ],
            [
                'c2dd2e64a672e5e1b82c019be848c2df',
                [
                    'return_code' => 'SUCCESS',
                    'return_msg' => 'OK',
                    'result_code' => 'SUCCESS',
                    'mch_id' => '1619665394',
                    'appid' => 'wx85bbb9f0e9460321',
                    'nonce_str' => 'iwMmaj4dS9slhxIH',
                    'prepay_id' => 'wx21170426533555ff1203597cc057e00000',
                    'trade_type' => 'JSAPI',
                ],
                'B59B4E7330BDA68F8B61D26EA1CCDB7A',
                'MD5',
            ],
        ];
    }
}
