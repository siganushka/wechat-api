<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\CallbackIp;

require __DIR__.'/_autoload.php';

$parsedResponse = $client->send(CallbackIp::class);
dd($parsedResponse);
