<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Payment\Query;

require __DIR__.'/_autoload.php';

$options = [
    'out_trade_no' => uniqid(),
    // 'transaction_id' => uniqid(),
];

$request = $factory->create(Query::class);
$result = $request->send($options);

dd($result);
