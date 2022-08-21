<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Payment;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationOptionsTest;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignatureUtilsTest extends TestCase
{
    private ?SignatureUtils $signatureUtils = null;

    protected function setUp(): void
    {
        $this->signatureUtils = SignatureUtils::create();
    }

    protected function tearDown(): void
    {
        $this->signatureUtils = null;
    }

    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->signatureUtils->configure($resolver);

        static::assertSame([
            'mchkey',
            'sign_type',
            'data',
        ], $resolver->getDefinedOptions());

        $data = ['foo' => 'hello'];
        static::assertSame([
            'sign_type' => 'MD5',
            'mchkey' => 'foo',
            'data' => $data,
        ], $resolver->resolve([
            'mchkey' => 'foo',
            'data' => $data,
        ]));

        static::assertSame([
            'sign_type' => 'HMAC-SHA256',
            'mchkey' => 'foo',
            'data' => $data,
        ], $resolver->resolve([
            'mchkey' => 'foo',
            'sign_type' => 'HMAC-SHA256',
            'data' => $data,
        ]));
    }

    public function testGenerate(): void
    {
        $data = ['foo' => 'hello'];

        $this->signatureUtils->using(ConfigurationOptionsTest::create());
        $signature = $this->signatureUtils->generate($data);

        static::assertSame('44C65B2286547046D4DEF419020A6425', $signature);
        static::assertTrue($this->signatureUtils->check('44C65B2286547046D4DEF419020A6425', $data));
    }

    /**
     * @dataProvider getSignatureProvider
     */
    public function testGenerateFromOptions(string $key, array $data, string $sign, string $signType): void
    {
        $options = [
            'mchkey' => $key,
            'sign_type' => $signType,
            'data' => $data,
        ];

        static::assertSame($sign, $this->signatureUtils->generateFromOptions($options));
        static::assertTrue($this->signatureUtils->checkFromOptions($sign, $options));
    }

    public function testMchkeyMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "mchkey" is missing');

        $this->signatureUtils->generateFromOptions([
            'data' => ['foo' => 'hello'],
        ]);
    }

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
