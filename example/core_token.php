<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\Token;

require __DIR__.'/_autoload.php';

$result = $client->send(Token::class);
dd($result);
