<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;

require __DIR__.'/_autoload.php';

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.createQRCode.html
 */
$options = [
    'path' => '/index/index',
    // 'width' => 200,
];

$parsedResponse = $client->send(Qrcode::class, $options);
dd($parsedResponse);

// 显示小程序码
// $base64Content = base64_encode($parsedResponse);
// echo sprintf('<img src="data:image/png;base64,%s" />', $base64Content);
