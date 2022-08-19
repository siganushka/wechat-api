<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;

require __DIR__.'/_autoload.php';

$options = [
    'code' => '033Ldf100yLlnO1Glo300fPv7N3Ldf1F',
];

$result = $client->send(SessionKey::class, $options);
dd($result);
