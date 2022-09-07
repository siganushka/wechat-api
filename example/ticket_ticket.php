<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\TokenOptions;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;

require __DIR__.'/_autoload.php';

$request = new Ticket();
$request->extend(new TokenOptions($configuration));

$result = $request->send();
dd($result);
