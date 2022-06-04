<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Extension;

use Siganushka\ApiClient\AbstractRequestExtension;
use Siganushka\ApiClient\RequestRegistryInterface;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Core\CallbackIp;
use Siganushka\ApiClient\Wechat\Core\ServerIp;
use Siganushka\ApiClient\Wechat\Message\Template\Message;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AccessTokenExtension extends AbstractRequestExtension
{
    private HttpClientInterface $httpClient;
    private RequestRegistryInterface $registry;

    public function __construct(HttpClientInterface $httpClient, RequestRegistryInterface $registry)
    {
        $this->httpClient = $httpClient;
        $this->registry = $registry;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $request = $this->registry->get(AccessToken::class);
        $request->setHttpClient($this->httpClient);

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
        ];
    }
}
