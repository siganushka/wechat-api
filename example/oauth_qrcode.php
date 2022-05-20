<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\HelperSet;
use Siganushka\ApiClient\Wechat\OAuth\Qrcode;

require __DIR__.'/_autoload.php';

if (!isset($_GET['code'])) {
    $options = [
        'redirect_uri' => HelperSet::getCurrentUrl(),
    ];

    $authorize = new Qrcode($configuration);
    $authorize->redirect($options);
    // dd($authorize->getAuthorizeUrl($options));

    exit;
}

$options = [
    'code' => $_GET['code'],
];

$result = $client->send(AccessToken::class, $options);
dd($result);
