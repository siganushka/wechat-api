<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Payment\Refund;

require __DIR__.'/_autoload.php';

$options = [
    'out_trade_no' => uniqid(),
    // 'transaction_id' => uniqid(),
    'out_refund_no' => uniqid(),
    'total_fee' => 2,
    'refund_fee' => 1,
];

$request = $factory->create(Refund::class);
$result = $request->send($options);

dd($result);
