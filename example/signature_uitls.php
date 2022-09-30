<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wxpay\ConfigurationExtension;
use Siganushka\ApiFactory\Wxpay\SignatureUtils;

require __DIR__.'/_autoload.php';

$signatureUtils = new SignatureUtils();
$signatureUtils->extend(new ConfigurationExtension($configuration));

$rawData = [
    'foo' => 'bar',
];

$options = [
    'data' => $rawData,
    // 'sign_type' => 'HMAC-SHA256', // MD5/HMAC-SHA256
];

$signature = $signatureUtils->generate($options);
dump('生成签名结果：', $signature);

$iSsignatureValid = $signatureUtils->verify($signature, $options);
dump('验证签名结果：', $iSsignatureValid);
