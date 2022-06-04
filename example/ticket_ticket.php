<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Ticket\Ticket;

require __DIR__.'/_autoload.php';

$parsedResponse = $client->send(Ticket::class);
dd($parsedResponse);
