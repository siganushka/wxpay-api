<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wxpay\ConfigurationExtension;
use Siganushka\ApiFactory\Wxpay\NotifyHandler;
use Siganushka\ApiFactory\Wxpay\OptionSet;
use Siganushka\ApiFactory\Wxpay\SignatureUtils;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/_autoload.php';

$parameters = [
    'appid' => 'xxx',
    'mch_id' => 'xxx',
    'openid' => 'xxx',
    'sign_type' => OptionSet::SIGN_TYPE_SHA256,
    'out_trade_no' => 'xxx',
    'bank_type' => 'xxx',
    'result_code' => 'xxx',
    'return_code' => 'xxx',
    'time_end' => 'xxx',
    'total_fee' => 'xxx',
    'trade_type' => 'xxx',
    'transaction_id' => 'xxx',
];

$signatureUtils = new SignatureUtils();
$signatureUtils->extend(new ConfigurationExtension($configuration));

$parameters['sign'] = $signatureUtils->generate([
    'data' => $parameters,
    'sign_type' => OptionSet::SIGN_TYPE_SHA256,
]);

$handler = new NotifyHandler();
$handler->extend(new ConfigurationExtension($configuration));

try {
    $data = $handler->handle($parameters, ['sign_type' => OptionSet::SIGN_TYPE_SHA256]);
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
