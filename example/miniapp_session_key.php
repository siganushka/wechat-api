<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\ConfigurationExtension;
use Siganushka\ApiFactory\Wechat\Miniapp\SessionKey;

require __DIR__.'/_autoload.php';

$options = [
    'code' => '033Ldf100yLlnO1Glo300fPv7N3Ldf1F',
];

$request = new SessionKey();
$request->extend(new ConfigurationExtension($configuration));

$result = $request->send($options);
dump($result);
