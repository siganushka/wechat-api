<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\GenericUtils;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken;
use Siganushka\ApiClient\Wechat\OAuth\Authorize;

require __DIR__.'/_autoload.php';

if (!isset($_GET['code'])) {
    $options = [
        'redirect_uri' => GenericUtils::getCurrentUrl(),
        'using_open_api' => true,
    ];

    $authorize = new Authorize($configuration);
    $authorize->redirect($options);
    // dd($authorize->getAuthorizeUrl($options));

    exit;
}

$options = [
    'code' => $_GET['code'],
    'using_open_api' => true,
];

$parsedResponse = $client->send(AccessToken::class, $options);
dd($parsedResponse);
