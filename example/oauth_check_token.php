<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\OAuth\CheckToken;

require __DIR__.'/_autoload.php';

$options = [
    'access_token' => 'foo',
    'openid' => 'oeBlc54IakibieYAIQYgQ5YOFO_U',
];

$request = $factory->create(CheckToken::class);
$result = $request->send($options);

dd($result);
