<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\TokenOptions;
use Siganushka\ApiClient\Wechat\Miniapp\WxacodeUnlimited;

require __DIR__.'/_autoload.php';

$options = [
    'scene' => 'foo',
    // 'page' => 'pages/index/index',
    // 'check_path' => false,
    // 'env_version' => 'develop', // release/trial/develop
    // 'width' => 200,
    // 'is_hyaline' => true,
    // 'auto_color' => false,
    // 'line_color' => '#FFB6C1',
];

$request = new WxacodeUnlimited();
$request->extend(new TokenOptions($configuration));

$result = $request->send($options);
dd($result);

// // 显示小程序码
// $base64Content = base64_encode($result);
// echo sprintf('<img src="data:image/png;base64,%s" />', $base64Content);
