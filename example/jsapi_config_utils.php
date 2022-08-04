<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;

require __DIR__.'/_autoload.php';

$apis = ['chooseImage'];

$configUtils = new ConfigUtils($configuration, $ticket);
$config = $configUtils->generate($apis, true);
dd($config);
