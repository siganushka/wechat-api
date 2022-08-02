<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;

require __DIR__.'/_autoload.php';

/** @var array{ ticket: string } */
$result = $client->send(Ticket::class);

$apis = ['chooseImage'];

$configUtils = new ConfigUtils($configuration);
$config = $configUtils->generate($result['ticket'], $apis, true);
dd($config);
