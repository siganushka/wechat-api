<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpClient\HttpClient;

$configFile = __DIR__.'/_config.php';
if (!is_file($configFile)) {
    exit('请复制 _config.php.dist 为 _config.php 并填写参数！');
}

require $configFile;
Debug::enable();

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        var_dump($vars);
        exit;
    }
}

$httpClient = HttpClient::create();
$cachePool = new FilesystemAdapter();

$configuration = new Configuration([
    'appid' => WECHAT_APPID,
    'secret' => WECHAT_SECRET,
    'open_appid' => WECHAT_OPEN_APPID,
    'open_secret' => WECHAT_OPEN_SECRET,
    'mchid' => WECHAT_MCHID,
    'mchkey' => WECHAT_MCHKEY,
    'client_cert_file' => WECHAT_CLIENT_CERT,
    'client_key_file' => WECHAT_CLIENT_KEY,
]);

$accessToken = new AccessToken($cachePool, $configuration);
$accessToken->setHttpClient($httpClient);

$ticket = new Ticket($cachePool, $accessToken);
$ticket->setHttpClient($httpClient);
