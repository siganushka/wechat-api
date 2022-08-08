<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Siganushka\ApiClient\Wechat\Ticket\TicketOptions;
use Siganushka\ApiClient\Wechat\WechatConfigurationOptions;

require __DIR__.'/_autoload.php';

$configUtils = new ConfigUtils();
$configUtils->extend(new WechatConfigurationOptions($configuration));
$configUtils->extend(new TicketOptions($configuration));

$config = $configUtils->generate();

dd($config);

// $configUtils = new ConfigUtils();
// $config = $configUtils->generate([
//     //（必填）参数，可由 WechatConfigurationOptions 扩展代替
//     'appid' => 'foo',
//     //（必填）参数，可由 TicketOptions 扩展代替
//     'ticket' => 'bar',
//     // 以下参数均为选填，可手动指定
//     'timestamp' => (string) time(),
//     'noncestr' => uniqid(),
//     'url' => 'http://localhost',
//     'apis' => ['chooseImage'],
//     'debug' => true,
// ]);

// dd($config);
