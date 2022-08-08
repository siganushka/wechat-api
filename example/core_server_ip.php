<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\ServerIp;

require __DIR__.'/_autoload.php';

$request = $factory->create(ServerIp::class);
$result = $request->send();

dd($result);
