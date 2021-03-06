<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Payment\SignatureUtils;

require __DIR__.'/_autoload.php';

$parameters = [
    'return_code' => 'SUCCESS',
    'return_msg' => 'OK',
    'result_code' => 'SUCCESS',
    'mch_id' => '1619665394',
    'appid' => 'wx85bbb9f0e9460321',
    'nonce_str' => 'pzBM7mKhwbuLzwHJ',
    'trade_type' => 'JSAPI',
];

$signatureUtils = new SignatureUtils($configuration);
$sign = $signatureUtils->generate($parameters);

// 生成 & 验证签名
dd($sign, $signatureUtils->check($parameters, $sign));

// 还可以验证参数中的签名是否有效
// dd($signatureUtils->checkParameters(array_merge($parameters, ['sign' => $sign])));
