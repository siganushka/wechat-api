<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\OAuth\AccessToken;
use Siganushka\ApiClient\Wechat\OAuth\Authorize;

require __DIR__.'/_autoload.php';

if (!isset($_GET['code'])) {
    $currentUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
        ($_SERVER['HTTP_HOST'] ?? 'localhost').
        ($_SERVER['REQUEST_URI'] ?? '');

    $options = [
        'redirect_uri' => $currentUrl,
        'scope' => 'snsapi_userinfo',
        'state' => 'foo',
    ];

    $authorize = new Authorize($configuration);
    $authorize->redirect($options);
    // dd($authorize->getAuthorizeUrl($options));

    exit;
}

$options = [
    'code' => $_GET['code'],
];

$result = $client->send(AccessToken::class, $options);
dd($result);
