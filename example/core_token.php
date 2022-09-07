<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Core\Token;

require __DIR__.'/_autoload.php';

$request = new Token();
$request->extend(new ConfigurationOptions($configuration));

$result = $request->send();
dd($result);
