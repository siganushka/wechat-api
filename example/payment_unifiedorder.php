<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Payment\Request\UnifiedorderRequest;

require __DIR__.'/_autoload.php';

$options = [
    'body' => '测试订单',
    'notify_url' => '/foo',
    'out_trade_no' => uniqid(),
    'total_fee' => 1,
    'trade_type' => 'JSAPI',
    'openid' => 'oaAle41wmUsogcsdUKZF9HJOPf5Q',
    // 'sign_type' => 'HMAC-SHA256',
];

$wrappedResponse = $client->send(UnifiedorderRequest::class, $options);
dd($wrappedResponse->getParsedBody());
