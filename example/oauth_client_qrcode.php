<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationExtension;
use Siganushka\ApiClient\Wechat\GenericUtils;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken;
use Siganushka\ApiClient\Wechat\OAuth\Qrcode;

require __DIR__.'/_autoload.php';

if (!isset($_GET['code'])) {
    $options = [
        'redirect_uri' => GenericUtils::getCurrentUrl(),
    ];

    $qrcode = new Qrcode();
    $qrcode->extend(new ConfigurationExtension($configuration));
    $qrcode->redirect($options);
    exit;
}

$options = [
    'code' => $_GET['code'],
];

$result = $client->send(AccessToken::class, $options);
dd($result);
