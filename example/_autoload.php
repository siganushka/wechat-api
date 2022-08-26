<?php

declare(strict_types=1);

use Siganushka\ApiClient\RequestClientBuilder;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\WechatExtension;
use Symfony\Component\ErrorHandler\Debug;

require __DIR__.'/../vendor/autoload.php';

Debug::enable();

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        var_dump($vars);
        exit;
    }
}

$configFile = __DIR__.'/_config.php';
if (!is_file($configFile)) {
    exit('请复制 _config.php.dist 为 _config.php 并填写参数！');
}

$configs = require $configFile;

// 小程序配置（默认）
$configuration = new Configuration($configs['miniapp']);
// 公众号配置
$mpConfiguration = new Configuration($configs['mp']);
// 开放平台配置
$openConfiguration = new Configuration($configs['open']);

$client = RequestClientBuilder::create()
    ->addExtension(new WechatExtension($configuration))
    ->build()
;

dd($client);
