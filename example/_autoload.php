<?php

declare(strict_types=1);

use Siganushka\ApiClient\RequestClientBuilder;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\ConfigurationManager;
use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Core\TokenOptions;
use Siganushka\ApiClient\Wechat\Ticket\TicketOptions;
use Siganushka\ApiClient\Wechat\WechatExtension;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\OptionsResolver\OptionsResolver;

require __DIR__.'/../vendor/autoload.php';

Debug::enable();

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        var_dump($vars);
        exit;
    }
}

$configFile = __DIR__.'/_config.php';
if (!is_file($configFile)) {
    exit('请复制 _config.php.dist 为 _config.php 并填写参数！');
}

$configurationManager = new ConfigurationManager('miniapp');
foreach (require $configFile as $name => $config) {
    $configurationManager->set($name, new Configuration($config));
}

$resolver = new OptionsResolver();

// $configurationOptions = new ConfigurationOptions($configurationManager);
// $configurationOptions->configure($resolver);
// dd('A', $resolver->getDefinedOptions(), $resolver->resolve(['using_config' => 'open']));

// $tokenOptions = new TokenOptions($configurationManager);
// $tokenOptions->configure($resolver);
// dd('B', $resolver->getDefinedOptions(), $resolver->resolve(['using_config' => 'open', 'token' => 'foo']));

// $ticketOptions = new TicketOptions($configurationManager);
// $ticketOptions->configure($resolver);
// dd('C', $resolver->getDefinedOptions(), $resolver->resolve(['using_config' => 'open', 'type' => 'wx_card']));

$client = RequestClientBuilder::create()
    ->addExtension(new WechatExtension($configurationManager))
    ->build()
;
