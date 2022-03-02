<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Payment\Request\TransferRequest;

require __DIR__.'/_autoload.php';

$options = [
    'partner_trade_no' => uniqid(),
    'openid' => 'oaAle41wmUsogcsdUKZF9HJOPf5Q',
    'amount' => 1,
    'desc' => '测试',
    'check_name' => 'FORCE_CHECK',
    're_user_name' => 'foo',
];

$wrappedResponse = $client->send(TransferRequest::class, $options);
dd($wrappedResponse->getParsedBody());
