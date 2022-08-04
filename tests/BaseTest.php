<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Ticket\Ticket;

abstract class BaseTest extends TestCase
{
    public function createMockAccessToken(): AccessToken
    {
        $accessToken = $this->createMock(AccessToken::class);
        $accessToken->method('send')->willReturn([
            'access_token' => 'foo',
            'expires_in' => 1024,
        ]);

        return $accessToken;
    }

    public function createMockTicket(): Ticket
    {
        $ticket = $this->createMock(Ticket::class);
        $ticket->method('send')->willReturn([
            'ticket' => 'test_ticket',
        ]);

        return $ticket;
    }
}
