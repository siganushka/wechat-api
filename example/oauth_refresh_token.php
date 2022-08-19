<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;

require __DIR__.'/_autoload.php';

$options = [
    'refresh_token' => 'your_refresh_token',
];

$result = $client->send(RefreshToken::class, $options);
dd($result);
