<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Payment\Unifiedorder;

require __DIR__.'/_autoload.php';

$options = [
    'body' => '测试订单',
    'notify_url' => 'http://localhost',
    'out_trade_no' => uniqid(),
    'total_fee' => 1,
    'trade_type' => 'JSAPI',
    'openid' => 'oaAle41wmUsogcsdUKZF9HJOPf5Q',
];

$request = new Unifiedorder();
$request->extend(new ConfigurationOptions($configuration));

$result = $request->send($options);
dd($result);
