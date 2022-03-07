<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Miniapp\Request\SessionKeyRequest;

require __DIR__.'/_autoload.php';

$options = [
    'js_code' => '00373VFa1G2nJC0t2iHa1MkYlo173VFK',
];

$result = $client->send(SessionKeyRequest::class, $options);
dd($result);
