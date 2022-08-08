<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Psr\Cache\CacheItemPoolInterface;
use Siganushka\ApiClient\RequestExtensionInterface;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Core\AccessTokenOptions;
use Siganushka\ApiClient\Wechat\Core\CallbackIp;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Siganushka\ApiClient\Wechat\Miniapp\WxacodeUnlimited;
use Siganushka\ApiClient\Wechat\OAuth\AccessToken as OAuthAccessToken;
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

class WechatExtension implements RequestExtensionInterface
{
    private ConfigurationManager $configurationManager;
    private CacheItemPoolInterface $cachePool;

    public function __construct(ConfigurationManager $configurationManager, CacheItemPoolInterface $cachePool = null)
    {
        $this->configurationManager = $configurationManager;
        $this->cachePool = $cachePool ?? new FilesystemAdapter();
    }

    public function loadRequests(): iterable
    {
        return [
            new AccessToken($this->cachePool),
            new ServerIp(),
            new CallbackIp(),
            new Qrcode(),
            new SessionKey($this->cachePool),
            new Wxacode(),
            new WxacodeUnlimited(),
            new OAuthAccessToken(),
            new CheckToken(),
            new RefreshToken(),
            new UserInfo(),
            new Query(),
            new Refund(),
            new Transfer(),
            new Unifiedorder(),
            new Message(),
            new Ticket($this->cachePool),
        ];
    }

    public function loadRequestOptionsExtensions(): iterable
    {
        return [
            new ConfigurationOptions($this->configurationManager),
            new AccessTokenOptions($this->configurationManager),
            new TicketOptions($this->configurationManager),
        ];
    }
}
