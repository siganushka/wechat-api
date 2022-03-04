<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\Request\AccessTokenRequest;

require __DIR__.'/_autoload.php';

$wrappedResponse = $client->send(AccessTokenRequest::class);
dd($wrappedResponse->getParsedResponse());
