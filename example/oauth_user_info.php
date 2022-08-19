<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\OAuth\UserInfo;

require __DIR__.'/_autoload.php';

// 注意，此处是用户授权后用户 access_token，不要和全局 access_token 混淆
$options = [
    'access_token' => 'your_access_token',
    'openid' => 'your_openid',
    // 'lang' => 'zh_CN',
];

$result = $client->send(UserInfo::class, $options);
dd($result);
