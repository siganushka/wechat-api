<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\RequestExtensionInterface;
use Siganushka\ApiClient\Wechat\Core\CallbackIp;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Siganushka\ApiClient\Wechat\Core\Token;
use Siganushka\ApiClient\Wechat\Core\TokenOptions;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Siganushka\ApiClient\Wechat\Miniapp\WxacodeUnlimited;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken;
use Siganushka\ApiClient\Wechat\OAuth\CheckToken;
use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;
use Siganushka\ApiClient\Wechat\OAuth\UserInfo;
use Siganushka\ApiClient\Wechat\Payment\Query;
use Siganushka\ApiClient\Wechat\Payment\Refund;
use Siganushka\ApiClient\Wechat\Payment\Transfer;
use Siganushka\ApiClient\Wechat\Payment\Unifiedorder;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Siganushka\ApiClient\Wechat\Ticket\TicketOptions;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WechatExtension implements RequestExtensionInterface
{
    private Configuration $configuration;
    private HttpClientInterface $httpClient;
    private CacheItemPoolInterface $cachePool;
    private SerializerInterface $serializer;

    public function __construct(
        Configuration $configuration,
        HttpClientInterface $httpClient = null,
        CacheItemPoolInterface $cachePool = null,
        SerializerInterface $serializer = null)
    {
        $this->configuration = $configuration;
        $this->httpClient = $httpClient ?? HttpClient::create();
        $this->cachePool = $cachePool ?? new FilesystemAdapter();
        $this->serializer = $serializer ?? new Serializer([], [new XmlEncoder(), new JsonEncoder()]);
    }

    public function loadRequests(): array
    {
        return [
            new Token($this->cachePool),
            new ServerIp(),
            new CallbackIp(),
            new Qrcode(),
            new SessionKey($this->cachePool),
            new Wxacode(),
            new WxacodeUnlimited(),
            new AccessToken(),
            new CheckToken(),
            new RefreshToken(),
            new UserInfo(),
            new Query($this->serializer),
            new Refund($this->serializer),
            new Transfer($this->serializer),
            new Unifiedorder($this->serializer),
            new Message(),
            new Ticket($this->cachePool),
        ];
    }

    public function loadOptionsExtensions(): array
    {
        return [
            new ConfigurationOptions($this->configuration),
            new TokenOptions($this->configuration, $this->httpClient, $this->cachePool),
            new TicketOptions($this->configuration, $this->httpClient, $this->cachePool),
        ];
    }
}
