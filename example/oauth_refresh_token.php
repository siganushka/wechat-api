<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;

require __DIR__.'/_autoload.php';

$options = [
    'refresh_token' => '57_qjajdoBeO1c3VW7tMfoCUVz7ds_CUHe4d8Ld20JD5UHDp3Yhbls8JNvBi5B2Wb-AvgA-qjFdme74l9z-0GCr1Q',
];

$result = $client->send(RefreshToken::class, $options);
dd($result);
