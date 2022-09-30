<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\Core\Ticket;
use Siganushka\ApiFactory\Wechat\Core\TokenExtension;

require __DIR__.'/_autoload.php';

$request = new Ticket();
$request->extend(new TokenExtension($configuration));

$result = $request->send();
dump($result);
