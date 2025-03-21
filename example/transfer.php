<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wxpay\Transfer;

require __DIR__.'/_autoload.php';

$options = [
    'partner_trade_no' => uniqid(),
    'openid' => 'oaAle41wmUsogcsdUKZF9HJOPf5Q',
    'amount' => 1,
    'desc' => '测试',
    // 'check_name' => 'FORCE_CHECK',
    // 're_user_name' => 'foo',
];

$request = new Transfer();
$request->extend($configurationExtension);

$result = $request->send($options);
dump('红包发送结果：', $result);
