<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;

require __DIR__.'/_autoload.php';

$options = [
    'path' => '/index/index',
    // 'env_version' => 'develop', // release/trial/develop
    // 'width' => 200,
    // 'auto_color' => true,
    // 'is_hyaline' => true,
    // 'line_color' => ['r' => 255, 'g' => 255, 'b' => 255],
];

$parsedResponse = $client->send(Wxacode::class, $options);
dd($parsedResponse);

// 显示小程序码
// $base64Content = base64_encode($parsedResponse);
// echo sprintf('<img src="data:image/png;base64,%s" />', $base64Content);
