<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\TicketOptions;
use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;

require __DIR__.'/_autoload.php';

$configUtils = ConfigUtils::create();
$configUtils->extend(new TicketOptions($configuration));

$config = $configUtils->generateFromOptions();
dd($config);
