<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wechat\ConfigurationOptions;
use Siganushka\ApiClient\Wechat\Payment\ParameterUtils;

require __DIR__.'/_autoload.php';

// 统一下单接口返回的 prepay_id 字段
$prepayId = 'wx17175520341037c035b014b2e89c520000';

$parameterUtils = ParameterUtils::create();
$parameterUtils->extend(new ConfigurationOptions($configuration));

$options = [
    'prepay_id' => $prepayId,
];

$jsapiParameter = $parameterUtils->jsapi($options);
$appParameter = $parameterUtils->app($options);

dd($jsapiParameter, $appParameter);
