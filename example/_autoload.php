<?php

declare(strict_types=1);

use Siganushka\ApiClient\RequestClient;
use Siganushka\ApiClient\RequestRegistry;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
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
use Symfony\Component\Serializer\Encoder\XmlEncoder;

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

$xmlEncoder = new XmlEncoder();
// dd($xmlEncoder);

$httpClient = HttpClient::create();
// dd($httpClient);

$cachePool = new FilesystemAdapter();
// dd($cachePool);

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
// dd($configuration);

$requests = [
    new AccessToken($configuration),
    new ServerIp($configuration),
    new SessionKey($configuration),
    new Transfer($configuration, $xmlEncoder),
    new Unifiedorder($configuration, $xmlEncoder),
    new OAuthAccessToken($configuration),
    new UserInfo(),
    new RefreshToken($configuration),
    new Ticket(),
    new CheckToken(),
    new Message(),
];

$registry = new RequestRegistry($requests);
// dd($registry);

$client = new RequestClient($httpClient, $cachePool, $registry);
// dd($client);
