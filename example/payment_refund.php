<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Payment\Refund;

require __DIR__.'/_autoload.php';

$options = [
    // 'transaction_id' => uniqid(),
    'out_trade_no' => '2106902686798370',
    'out_refund_no' => uniqid(),
    'total_fee' => 2,
    'refund_fee' => 1,
];

$request = new Refund();
$request->extend(new ConfigurationOptions($configuration));

$result = $request->send($options);
dd($result);
