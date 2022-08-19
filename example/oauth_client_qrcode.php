<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\GenericUtils;
use Siganushka\ApiClient\Wechat\OAuth\Qrcode;

require __DIR__.'/_autoload.php';

$client = new Qrcode();
$client->using(new ConfigurationOptions($openConfiguration));

if (!isset($_GET['code'])) {
    $options = [
        'redirect_uri' => GenericUtils::getCurrentUrl(),
    ];

    $redirectUrl = $client->getRedirectUrl($options);
    header(sprintf('Location: %s', $redirectUrl));
    exit;
}

// 获取已授权用户的 access_token
$accessToken = $client->getAccessToken([
    'code' => $_GET['code'],
]);
dump('getAccessToken', $accessToken);

// 根据已授权用户的 access_token 获取用户信息
$userInfo = $client->getUserInfo([
    'access_token' => $accessToken['access_token'],
    'openid' => $accessToken['openid'],
]);
dump('getUserInfo', $userInfo);

// 刷新当前已授权用户的 access_token
$newAccessToken = $client->refreshToken([
    'refresh_token' => $accessToken['refresh_token'],
]);
dump('refreshToken', $newAccessToken);

// 检测当前已授权用户的 access_token 是否有效
$checkToken = $client->checkToken([
    'access_token' => $newAccessToken['access_token'],
    'openid' => $newAccessToken['openid'],
]);
dump('checkToken', $checkToken);
