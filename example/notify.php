<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wxpay\NotifyHandler;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/_autoload.php';

// 微信支付结果通知请求对象
$request = Request::createFromGlobals();

$handler = new NotifyHandler();
$handler->extend($configurationExtension);

try {
    $data = $handler->handle($request);
} catch (Throwable $th) {
    $handler->fail($th->getMessage())->send();
}

// 处理其它业务逻辑...
// $order = $orderRepository->find($data['out_trade_no']);
// if (!$order) {
//     $handler->fail('订单号无效！');
// }

$handler->success('ok')->send();
