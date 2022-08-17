<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\GenericUtils;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken;
use Siganushka\ApiClient\Wechat\OAuth\Client;

require __DIR__.'/_autoload.php';

if (!isset($_GET['code'])) {
    $options = [
        'redirect_uri' => GenericUtils::getCurrentUrl(),
        'scope' => 'snsapi_userinfo',
    ];

    $client = new Client();
    $client->using(new ConfigurationOptions($configurationManager));

    header(sprintf('Location: %s', $client->getRedirectUrl($options)));
    exit;
}

$options = [
    'code' => $_GET['code'],
];

$result = $client->send(AccessToken::class, $options);
dd($result);
