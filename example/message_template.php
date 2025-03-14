<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wechat\Core\TokenExtension;
use Siganushka\ApiFactory\Wechat\Message\Template;
use Siganushka\ApiFactory\Wechat\Message\TemplateMessage;

require __DIR__.'/_autoload.php';

$template = new Template('jEK74yhiRjj4zH3R3sjiHcGOsHpigsVbNiquLCTngz0');
$template->addData('keyword1', 'value 111');
$template->addData('keyword2', 'value 222');
$template->addData('keyword3', 'value 333');

$options = [
    'template' => $template,
    'touser' => 'o_rGJ5xuO3r4Zh9NGKRiVTzSwbfM',
    // 'url' => 'https://cn.bing.com',
    // 'miniprogram' => [
    //     'appid' => 'foo',
    //     'pagepath' => 'bar'
    // ],
    // 'client_msg_id' => uniqid(),
];

$request = new TemplateMessage();
$request->extend(new TokenExtension($mpConfiguration));

$result = $request->send($options);
dump($result);
