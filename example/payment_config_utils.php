<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Payment\ConfigUtils;

require __DIR__.'/_autoload.php';

// 统一下单接口返回数据，只需要用到 prepay_id 字段
$parameters = [
    'return_code' => 'SUCCESS',
    'return_msg' => 'OK',
    'result_code' => 'SUCCESS',
    'mch_id' => '1619665394',
    'appid' => 'wx85bbb9f0e9460321',
    'nonce_str' => 'pzBM7mKhwbuLzwHJ',
    // 'sign' => 'BC7D58E6B896BE71D02FBCF019122E87',
    'prepay_id' => 'wx17175520341037c035b014b2e89c520000',
    'trade_type' => 'JSAPI',
];

$configUtils = new ConfigUtils($configuration);
dd($configUtils->generate($parameters['prepay_id']));
