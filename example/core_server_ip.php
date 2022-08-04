<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\ServerIp;

require __DIR__.'/_autoload.php';

$request = new ServerIp($accessToken);
$request->setHttpClient($httpClient);

$result = $request->send();
dd($result);
