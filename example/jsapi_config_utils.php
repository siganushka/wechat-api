<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Siganushka\ApiClient\Wechat\Ticket\TicketOptions;

require __DIR__.'/_autoload.php';

$configUtils = new ConfigUtils();
$configUtils->using(new TicketOptions($configuration));

$config = $configUtils->generateFromOptions();
dd($config);
