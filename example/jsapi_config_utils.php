<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationExtension;
use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Siganushka\ApiClient\Wechat\Ticket\TicketExtension;

require __DIR__.'/_autoload.php';

$configUtils = new ConfigUtils();
$configUtils->extend(new ConfigurationExtension($configuration));
$configUtils->extend(new TicketExtension($configuration));

$config = $configUtils->generate(['chooseImage'], true);
dd($config);

// 自定义所有参数
// $configUtils = new ConfigUtils();
// $config = $configUtils->generateFromOptions([
//     //（必填）参数，可由 ConfigurationExtension 扩展代替
//     'appid' => 'foo',
//     'secret' => 'bar',
//     //（必填）参数，可由 TicketExtension 扩展代替
//     'ticket' => 'bar',
//     // 以下参数均为选填，可手动指定
//     'timestamp' => (string) time(),
//     'noncestr' => uniqid(),
//     'url' => 'http://localhost',
//     'apis' => ['chooseImage'],
//     'debug' => true,
// ]);

// dd($config);
