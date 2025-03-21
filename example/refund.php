<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wxpay\Refund;

require __DIR__.'/_autoload.php';

$options = [
    'out_trade_no' => '2106902686798370',
    'out_refund_no' => '2106902686798370',
    'total_fee' => 2,
    'refund_fee' => 1,
];

$request = new Refund();
$request->extend($configurationExtension);

$result = $request->send($options);
dump('订单退款结果：', $result);
