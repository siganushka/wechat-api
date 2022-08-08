<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\AccessToken;

require __DIR__.'/_autoload.php';

$request = $factory->create(AccessToken::class);
$result = $request->send();

dd($result);
