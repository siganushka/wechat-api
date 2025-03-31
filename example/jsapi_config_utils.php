<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\ConfigurationExtension;
use Siganushka\ApiFactory\Wechat\Core\TicketExtension;
use Siganushka\ApiFactory\Wechat\Jsapi\ConfigUtils;

require __DIR__.'/_autoload.php';

$configUtils = new ConfigUtils();
$configUtils->extend(new ConfigurationExtension($configuration));
$configUtils->extend(new TicketExtension($configuration));

$options = [
    'apis' => ['chooseImage', 'previewImage'],
    'debug' => true,
];

$config = $configUtils->generate($options);
dump($config);
