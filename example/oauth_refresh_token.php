<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\ConfigurationExtension;
use Siganushka\ApiFactory\Wechat\OAuth\RefreshToken;

require __DIR__.'/_autoload.php';

$options = [
    'refresh_token' => 'your_refresh_token',
];

$request = new RefreshToken();
$request->extend(new ConfigurationExtension($configuration));

$result = $request->send($options);
dump($result);
