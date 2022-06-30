<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Extension;

use Siganushka\ApiClient\AbstractRequestExtension;
use Siganushka\ApiClient\RequestRegistryInterface;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Core\CallbackIp;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Siganushka\ApiClient\Wechat\Miniapp\Qrcode;
use Siganushka\ApiClient\Wechat\Miniapp\Wxacode;
use Siganushka\ApiClient\Wechat\Template\Message;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessTokenExtension extends AbstractRequestExtension
{
    private RequestRegistryInterface $registry;

    public function __construct(RequestRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $request = $this->registry->get(AccessToken::class);
        /** @var array{ access_token: string } */
        $parsedResponse = $request->send();

        $resolver->setDefault('access_token', $parsedResponse['access_token']);
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
