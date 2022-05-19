<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\OAuth\CheckToken;

require __DIR__.'/_autoload.php';

$options = [
    'access_token' => '57_rojSbXULqiF6RJMpurXVHMUUkQQX445B-ROFAZO2V2CdsJK7LJPng9dTMrKq-pcpAFHB5CG-xTB4YbDFjHHRdA',
    'openid' => 'oS7zK6Q1nUvE1JD4sWAUwi3rmAkg',
];

$result = $client->send(CheckToken::class, $options);
dd($result);
