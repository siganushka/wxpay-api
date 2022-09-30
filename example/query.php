<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wxpay\ConfigurationExtension;
use Siganushka\ApiFactory\Wxpay\Query;

require __DIR__.'/_autoload.php';

$options = [
    // 'transaction_id' => '4200001545202209232366753480',
    'out_trade_no' => '2226544775667782',
];

$request = new Query();
$request->extend(new ConfigurationExtension($configuration));

$result = $request->send($options);
dump('订单查询结果：', $result);
