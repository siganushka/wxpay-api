<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wxpay\Unifiedorder;

require __DIR__.'/_autoload.php';

$options = [
    'body' => '测试订单',
    'notify_url' => 'http://localhost',
    'out_trade_no' => uniqid(),
    'total_fee' => 1,
    'trade_type' => 'APP',
];

$request = new Unifiedorder();
$request->extend($configurationExtension);

$result = $request->send($options);
dump('统一下单结果：', $result);
