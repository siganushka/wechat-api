<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\OAuth\UserInfo;

require __DIR__.'/_autoload.php';

$options = [
    'access_token' => '57_DOas9TmX1eF-GlBM9FD_sEMvSCSCIRC2RvadqLKmBnfZPfXLH38DxtL2YUj6HpfM2iRT2WJ32qopz6HGLTy36mL3c8jVRqzdjG_8SskmJH8',
    'openid' => 'oeBlc54IakibieYAIQYgQ5YOFO_U',
];

$parsedResponse = $client->send(UserInfo::class, $options);
dd($parsedResponse);
