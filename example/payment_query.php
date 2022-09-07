<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Payment\Query;

require __DIR__.'/_autoload.php';

$options = [
    // 'transaction_id' => '4200001561202208182119001028',
    'out_trade_no' => '2222944548774646',
];

$request = new Query();
$request->extend(new ConfigurationOptions($configuration));

$result = $request->send($options);
dd($result);
