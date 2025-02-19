<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\Core\TokenExtension;
use Siganushka\ApiFactory\Wechat\Message\SubscribeMessage;
use Siganushka\ApiFactory\Wechat\Message\Template;

require __DIR__.'/_autoload.php';

$template = new Template('jEK74yhiRjj4zH3R3sjiHcGOsHpigsVbNiquLCTngz0');
$template->addData('first', 'first 111');
$template->addData('keyword1', 'value 111');
$template->addData('keyword2', 'value 222');
$template->addData('keyword3', 'value 333');
$template->addData('remark', 'remark 111');

$options = [
    'template' => $template,
    'touser' => 'o37Dw69Mx6bYLtRoIcSmWJuq-1kc',
    // 'page' => '/pages/index/index?foo=bar',
    // 'miniprogram_state' => 'developer',
    // 'lang' => 'en_US',
];

$request = new SubscribeMessage();
$request->extend(new TokenExtension($configuration));

$result = $request->send($options);
dump($result);
