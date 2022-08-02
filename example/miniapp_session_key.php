<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;

require __DIR__.'/_autoload.php';

$options = [
    'code' => '053GLSll2wZij94xpOol2mqhxo4GLSlv',
];

$result = $client->send(SessionKey::class, $options);
dd($result);
