<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wxpay\SignatureUtils;

require __DIR__.'/_autoload.php';

$signatureUtils = new SignatureUtils();
$signatureUtils->extend($configurationExtension);

// 待待签名数据
$data = [
    'foo' => 'bar',
];

$signature = $signatureUtils->generate($data);
dump('生成签名结果：', $signature);

$isValid = $signatureUtils->verify($signature, $data);
dump('验证签名结果：', $isValid);
