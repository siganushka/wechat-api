<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\GenericUtils;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken;
use Siganushka\ApiClient\Wechat\OAuth\Client;

require __DIR__.'/_autoload.php';

if (!isset($_GET['code'])) {
    $options = [
        'redirect_uri' => GenericUtils::getCurrentUrl(),
        'scope' => 'snsapi_userinfo',
    ];

    $client = new Client($configuration);
    $client->redirect($options);
    exit;
}

$options = [
    'code' => $_GET['code'],
];

$request = new AccessToken($configuration);
$request->setHttpClient($httpClient);

$result = $request->send($options);
dd($result);
