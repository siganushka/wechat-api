<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationExtension;
use Siganushka\ApiClient\Wechat\Payment\ConfigUtils;

require __DIR__.'/_autoload.php';

// 统一下单接口返回的 prepay_id 字段
$prepayId = 'wx17175520341037c035b014b2e89c520000';

$configUtils = new ConfigUtils();
$configUtils->extend(new ConfigurationExtension($configuration));

$config = $configUtils->generate($prepayId);
dd($config);
