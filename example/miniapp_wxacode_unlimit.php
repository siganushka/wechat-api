<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Miniapp\WxacodeUnlimited;

require __DIR__.'/_autoload.php';

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.getUnlimited.html
 */
$options = [
    'scene' => 'foo',
    // 'page' => '/pages/index/index',
    // 'check_path' => false,
    // 'env_version' => 'develop', // release/trial/develop
    // 'width' => 200,
    // 'auto_color' => true,
    // 'is_hyaline' => true,
    // 'line_color' => ['r' => 255, 'g' => 255, 'b' => 255],
];

$result = $client->send(WxacodeUnlimited::class, $options);
dd($result);

// 显示小程序码
// $base64Content = base64_encode($result);
// echo sprintf('<img src="data:image/png;base64,%s" />', $base64Content);
