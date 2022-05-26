<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Core\CallbackIp;

require __DIR__.'/_autoload.php';

$result = $client->send(AccessToken::class);
$options = [
    'access_token' => $result['access_token'],
];

$result = $client->send(CallbackIp::class, $options);
dd($result);
