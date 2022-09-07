<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Siganushka\ApiClient\Wechat\Core\TokenOptions;

require __DIR__.'/_autoload.php';

$request = new ServerIp();
$request->extend(new TokenOptions($configuration));

$result = $request->send();
dd($result);
