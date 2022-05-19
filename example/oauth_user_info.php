<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\OAuth\UserInfo;

require __DIR__.'/_autoload.php';

$options = [
    'access_token' => '57_bnoZR8Y3M7unvodqU0S37WmodHaF5c3jRVW3XcYo7ZzcwF5mJtyzFHGd3oC6SA19OCFmpL8GXUCDlvQI6JC69A',
    'openid' => 'oS7zK6Q1nUvE1JD4sWAUwi3rmAkg',
];

$result = $client->send(UserInfo::class, $options);
dd($result);
