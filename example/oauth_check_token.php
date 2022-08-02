<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\OAuth\CheckToken;

require __DIR__.'/_autoload.php';

$options = [
    'access_token' => '57_X5L14BQvMcS6T39gWcUPE_XxmFEHoXWpAWmbwvRdAz7inJ9pz8aqSqTgfHj4yv8BNtMoRBSk014jA1bOM6j1B2WrIFJz8EYAuR69Wi3zDzw',
    'openid' => 'oeBlc54IakibieYAIQYgQ5YOFO_U',
];

$result = $client->send(CheckToken::class, $options);
dd($result);
