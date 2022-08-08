<?php

declare(strict_types=1);

use Siganushka\ApiClient\RequestFactoryBuilder;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\ConfigurationManager;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
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

$configurationManager = new ConfigurationManager('miniapp');
foreach ($configs as $name => $values) {
    $configurationManager->set($name, new Configuration($values));
}

$configurationOptions = new ConfigurationOptions($configurationManager);

$factory = RequestFactoryBuilder::create()
    ->addExtension(new WechatExtension($configurationManager))
    ->getFactory()
;
