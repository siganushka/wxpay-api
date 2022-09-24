<?php

declare(strict_types=1);

use Siganushka\ApiClient\Wxpay\ConfigurationOptions;
use Siganushka\ApiClient\Wxpay\Query;

require __DIR__.'/_autoload.php';

$options = [
    // 'transaction_id' => '4200001545202209232366753480',
    'out_trade_no' => '2226544775667782',
];

$request = new Query();
$request->extend(new ConfigurationOptions($configuration));

$result = $request->send($options);
dd($result);
