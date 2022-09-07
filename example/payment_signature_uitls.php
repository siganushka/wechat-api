<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;

require __DIR__.'/_autoload.php';

$data = [
    'return_code' => 'SUCCESS',
    'return_msg' => 'OK',
    'result_code' => 'SUCCESS',
    'mch_id' => '1619665394',
    'appid' => 'wx85bbb9f0e9460321',
    'nonce_str' => 'pzBM7mKhwbuLzwHJ',
    'trade_type' => 'JSAPI',
];

$signatureUtils = SignatureUtils::create();
$signatureUtils->extend(new ConfigurationOptions($configuration));

// 生成签名
$sign = $signatureUtils->generate($data);

// 生成 & 验证签名
dd($sign, $signatureUtils->check($sign, $data));
