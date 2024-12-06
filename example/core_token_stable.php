<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\ConfigurationExtension;
use Siganushka\ApiFactory\Wechat\Core\TokenStable;

require __DIR__.'/_autoload.php';

$request = new TokenStable();
$request->extend(new ConfigurationExtension($configuration));

$result = $request->send();
dump($result);
