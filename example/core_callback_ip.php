<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\Core\CallbackIp;
use Siganushka\ApiFactory\Wechat\Core\TokenExtension;

require __DIR__.'/_autoload.php';

$request = new CallbackIp();
$request->extend(new TokenExtension($configuration));

$result = $request->send();
dump($result);
