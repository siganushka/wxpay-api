<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wxpay\ParameterUtils;

require __DIR__.'/_autoload.php';

// 统一下单接口返回的 prepay_id 字段
$prepayId = 'wx17175520341037c035b014b2e89c520000';

$parameterUtils = new ParameterUtils();
$parameterUtils->extend($configurationExtension);

$options = [
    'prepay_id' => $prepayId,
];

$jsapiParameter = $parameterUtils->jsapi($options);
dump('JSAPI 支付参数：', $jsapiParameter);

$appParameter = $parameterUtils->app($options);
dump('APP 支付参数：', $appParameter);
