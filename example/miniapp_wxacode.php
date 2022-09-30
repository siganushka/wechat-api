<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\Core\TokenExtension;
use Siganushka\ApiFactory\Wechat\Miniapp\Wxacode;

require __DIR__.'/_autoload.php';

$options = [
    'path' => '/index/index',
    // 'env_version' => 'develop', // release/trial/develop
    // 'width' => 200,
    // 'is_hyaline' => true,
    // 'auto_color' => false,
    'line_color' => '#FFB6C1',
];

$request = new Wxacode();
$request->extend(new TokenExtension($configuration));

$result = $request->send($options);
dump($result);

// // 显示小程序码
// $base64Content = base64_encode($result);
// echo sprintf('<img src="data:image/png;base64,%s" />', $base64Content);
