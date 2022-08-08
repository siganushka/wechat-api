<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Ticket\Ticket;

require __DIR__.'/_autoload.php';

$request = $factory->create(Ticket::class);
$result = $request->send();

dd($result);
