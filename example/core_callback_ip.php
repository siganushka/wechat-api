<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\CallbackIp;
use Siganushka\ApiClient\Wechat\Core\TokenOptions;

require __DIR__.'/_autoload.php';

$request = new CallbackIp();
$request->extend(new TokenOptions($configuration));

$result = $request->send();
dd($result);
