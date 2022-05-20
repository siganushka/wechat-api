<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Siganushka\ApiClient\Wechat\Message\Template\Send;
use Siganushka\ApiClient\Wechat\Message\Template\Template;

require __DIR__.'/_autoload.php';

$template = new Template('jEK74yhiRjj4zH3R3sjiHcGOsHpigsVbNiquLCTngz0');
$template->addData('first', 'first 111', '#ff0000');
$template->addData('keyword1', 'value 111');
$template->addData('keyword2', 'value 222');
$template->addData('keyword3', 'value 333');
$template->addData('remark', 'remark 111', '#0000ff');

$result = $client->send(AccessToken::class);
$options = [
    'access_token' => $result['access_token'],
    'template' => $template,
    'touser' => 'o_rGJ5xuO3r4Zh9NGKRiVTzSwbfM',
    // 'url' => 'https://cn.bing.com',
    // 'miniprogram' => [
    //     'appid' => 'foo',
    //     'pagepath' => 'bar'
    // ],
];

$result = $client->send(Send::class, $options);
dd($result);
