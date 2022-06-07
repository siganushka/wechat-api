<?php

declare(strict_types=1);

use Siganushka\ApiClient\ApiClient;
use Siganushka\ApiClient\RequestRegistry;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Core\CallbackIp;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Siganushka\ApiClient\Wechat\Extension\AccessTokenExtension;
use Siganushka\ApiClient\Wechat\Message\Template\Message;
use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken as OAuthAccessToken;
use Siganushka\ApiClient\Wechat\OAuth\CheckToken;
use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;
use Siganushka\ApiClient\Wechat\OAuth\UserInfo;
use Siganushka\ApiClient\Wechat\Payment\Transfer;
use Siganushka\ApiClient\Wechat\Payment\Unifiedorder;
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

$requests = [
    new AccessToken($cachePool, $configuration),
    new ServerIp(),
    new CallbackIp(),
    new SessionKey($cachePool, $configuration),
    new Transfer($configuration),
    new Unifiedorder($configuration),
    new OAuthAccessToken($configuration),
    new UserInfo(),
    new RefreshToken($configuration),
    new Ticket($cachePool),
    new CheckToken(),
    new Message(),
];

$registry = new RequestRegistry($requests);

$extensions = [
    new AccessTokenExtension($httpClient, $registry),
];

$client = new ApiClient($httpClient, $registry, $extensions);
