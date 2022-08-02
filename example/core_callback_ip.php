<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\CallbackIp;

require __DIR__.'/_autoload.php';

$result = $client->send(CallbackIp::class);
dd($result);
