<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;

require __DIR__.'/_autoload.php';

$options = [
    'js_code' => '00373VFa1G2nJC0t2iHa1MkYlo173VFK',
];

$result = $client->send(SessionKey::class, $options);
dd($result);
