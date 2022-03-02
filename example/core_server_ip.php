<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\Request\AccessTokenRequest;
use Siganushka\ApiClient\Wechat\Core\Request\ServerIpRequest;

require __DIR__.'/_autoload.php';

$wrappedResponse = $client->send(AccessTokenRequest::class);
$result = $wrappedResponse->getParsedBody();

$options = [
    'access_token' => $result['access_token'],
];

$wrappedResponse = $client->send(ServerIpRequest::class, $options);
dd($wrappedResponse->getParsedBody());
