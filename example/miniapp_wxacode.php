<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;

require __DIR__.'/_autoload.php';

$options = [
    'path' => '/index/index',
    // 'env_version' => 'release', // release/trial/develop
    // 'width' => 200,
    // 'auto_color' => false,
    // 'is_hyaline' => false,
    // 'line_color' => ['r' => 0, 'g' => 0, 'b' => 0],
];

$parsedResponse = $client->send(Wxacode::class, $options);
dd($parsedResponse);
