<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;

require __DIR__.'/_autoload.php';

$options = [
    'refresh_token' => '57_h7pVMaRRuk9B0yQzq9-g3lND_kuhY9eAlZ0evBqKpgV_HUXnpiNSDCPFbz1KphbFOMYzNjUEdRo5c0FFo-jK1ldYBag0eiqj05XrvEs7tdU',
    // 'using_open_api' => true,
];

$parsedResponse = $client->send(RefreshToken::class, $options);
dd($parsedResponse);
