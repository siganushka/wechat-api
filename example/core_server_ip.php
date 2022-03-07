<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\Request\AccessTokenRequest;
use Siganushka\ApiClient\Wechat\Core\Request\ServerIpRequest;

require __DIR__.'/_autoload.php';

$result = $client->send(AccessTokenRequest::class);
$options = [
    'access_token' => $result['access_token'],
];

$result = $client->send(ServerIpRequest::class, $options);
dd($result);
