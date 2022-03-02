<?php

declare(strict_types=1);

use Siganushka\ApiClient\RequestClient;
use Siganushka\ApiClient\RequestRegistry;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\Core\Request\AccessTokenRequest;
use Siganushka\ApiClient\Wechat\Core\Request\ServerIpRequest;
use Siganushka\ApiClient\Wechat\Miniapp\Request\SessionKeyRequest;
use Siganushka\ApiClient\Wechat\Payment\Request\TransferRequest;
use Siganushka\ApiClient\Wechat\Payment\Request\UnifiedorderRequest;
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

$configuration = new Configuration([
    'appid' => WECHAT_APPID,
    'appsecret' => WECHAT_APPSECRET,
    'mchid' => WECHAT_MCHID,
    'mchkey' => WECHAT_MCHKEY,
    'client_cert_file' => WECHAT_CLIENT_CERT,
    'client_key_file' => WECHAT_CLIENT_KEY,
]);
// dd($configuration);

$requests = [
    new AccessTokenRequest($configuration),
    new ServerIpRequest($configuration),
    new SessionKeyRequest($configuration),
    new TransferRequest($configuration, $xmlEncoder),
    new UnifiedorderRequest($configuration, $xmlEncoder),
];

$registry = new RequestRegistry($requests);
// dd($registry);

$client = new RequestClient($httpClient, $registry);
// dd($client);
