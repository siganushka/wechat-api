<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\OAuth\UserInfo;

require __DIR__.'/_autoload.php';

// OAuth 授权通过 code 换取到的 access_token，每个 code 只能使用一次
$options = [
    'access_token' => '57_DOas9TmX1eF-GlBM9FD_sEMvSCSCIRC2RvadqLKmBnfZPfXLH38DxtL2YUj6HpfM2iRT2WJ32qopz6HGLTy36mL3c8jVRqzdjG_8SskmJH8',
    'openid' => 'oeBlc54IakibieYAIQYgQ5YOFO_U',
];

$result = $client->send(UserInfo::class, $options);
dd($result);
