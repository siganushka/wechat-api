<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\CallbackIp;

require __DIR__.'/_autoload.php';

$request = $factory->create(CallbackIp::class);
$result = $request->send();

dd($result);
