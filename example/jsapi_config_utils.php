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
