<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;

require __DIR__.'/_autoload.php';

$options = [
    'refresh_token' => 'your_refresh_token',
];

$request = new RefreshToken();
$request->extend(new ConfigurationOptions($configuration));

$result = $request->send($options);
dd($result);
