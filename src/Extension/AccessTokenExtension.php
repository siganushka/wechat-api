<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Extension;

use Siganushka\ApiClient\AbstractRequestExtension;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Core\CallbackIp;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AccessTokenExtension extends AbstractRequestExtension
{
    private AccessToken $accessToken;

    public function __construct(HttpClientInterface $httpClient, AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->accessToken->setHttpClient($httpClient);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        /** @var array{ access_token: string } */
        $result = $this->accessToken->send();

        $resolver->setDefault('access_token', $result['access_token']);
    }

    /**
     * @return iterable<string>
     */
    public static function getExtendedRequests(): iterable
    {
        return [
            Ticket::class,
            ServerIp::class,
            CallbackIp::class,
            Message::class,
            Wxacode::class,
            Qrcode::class,
        ];
    }
}
