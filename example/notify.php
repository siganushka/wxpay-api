<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wxpay\ConfigurationExtension;
use Siganushka\ApiFactory\Wxpay\NotifyHandler;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/_autoload.php';

// 微信支付结果通知数据
$data = [
    'appid' => 'xxx',
    'mch_id' => 'xxx',
    'openid' => 'xxx',
    'out_trade_no' => 'xxx',
    'bank_type' => 'xxx',
    'result_code' => 'xxx',
    'return_code' => 'xxx',
    'time_end' => 'xxx',
    'total_fee' => 'xxx',
    'trade_type' => 'xxx',
    'transaction_id' => 'xxx',
    'sign' => '513D149C277A9F274C614099161CBC65',
];

$handler = new NotifyHandler();
$handler->extend(new ConfigurationExtension($configuration));

try {
    $data = $handler->handle($data);
    // $data = $handler->handleRequest(Request::createFromGlobals());
} catch (Throwable $th) {
    $handler->fail($th->getMessage());
}

// 处理其它业务逻辑...
// $order = $orderRepository->find($data['out_trade_no']);
// if (!$order) {
//     $handler->fail('订单号无效！');
// }

$handler->success('ok');
