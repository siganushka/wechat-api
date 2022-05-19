<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Ticket\Ticket;

require __DIR__.'/_autoload.php';

$options = [
    'access_token' => '57_e8a6BULoOYXFH1CqllPvCJWpLwe7x4gkxzJfOv2yv75kD641K_r7DNpfTSb66iKlQR_H-TmNytQffoIiqHctxQncsKnXgauhZUti15qdq0c6OH6kKnt9DwSKz1n5rPRPUz9zvsJKXZYQ1F2HFIYdACATWM',
    'type' => 'jsapi',
];

$result = $client->send(Ticket::class, $options);
dd($result);
