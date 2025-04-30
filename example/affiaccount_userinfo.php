<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\Affiaccount\UserInfo;
use Siganushka\ApiFactory\Wechat\Core\TokenExtension;

require __DIR__.'/_autoload.php';

$options = [
    'openid' => 'o_rGJ51crP8yiSF8AhOYYIQ_VQzo',
];

$request = new UserInfo();
$request->extend(new TokenExtension($mpConfiguration));

$result = $request->send($options);
dump($result);
