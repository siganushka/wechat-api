<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\Affiaccount\User;
use Siganushka\ApiFactory\Wechat\Core\TokenExtension;

require __DIR__.'/_autoload.php';

$request = new User();
$request->extend(new TokenExtension($mpConfiguration));

$result = $request->send();
dump($result);
