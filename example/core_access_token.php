<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\AccessToken;

require __DIR__.'/_autoload.php';

$result = $client->send(AccessToken::class);
dd($result);
