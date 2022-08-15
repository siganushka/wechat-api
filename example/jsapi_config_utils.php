<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Siganushka\ApiClient\Wechat\Ticket\TicketOptions;

require __DIR__.'/_autoload.php';

$configUtils = new ConfigUtils($configurationManager);
$configUtils->using(new TicketOptions($configurationManager));

$config = $configUtils->generate();
dd($config);
