<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\Core\TicketExtension;
use Siganushka\ApiFactory\Wechat\Jsapi\ConfigUtils;

require __DIR__.'/_autoload.php';

$configUtils = new ConfigUtils();
$configUtils->extend(new TicketExtension($configuration));

$config = $configUtils->generate();
dump($config);
