<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;

require __DIR__.'/_autoload.php';

$options = [
    'code' => '033Ldf100yLlnO1Glo300fPv7N3Ldf1F',
];

$request = new SessionKey();
$request->extend(new ConfigurationOptions($configuration));

$result = $request->send($options);
dd($result);
