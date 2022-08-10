<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Payment\Query;

require __DIR__.'/_autoload.php';

$options = [
    'transaction_id' => uniqid(),
    // 'out_trade_no' => uniqid(),
];

$result = $client->send(Query::class, $options);
dd($result);
