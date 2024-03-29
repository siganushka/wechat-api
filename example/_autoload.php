<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\Configuration;
use Symfony\Component\ErrorHandler\Debug;

require __DIR__.'/../vendor/autoload.php';

Debug::enable();

if (!function_exists('dump')) {
    function dump(...$vars): void
    {
        var_dump($vars);
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
