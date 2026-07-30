<?php

declare(strict_types=1);

use Siganushka\ApiFactory\Wxpay\Configuration;
use Siganushka\ApiFactory\Wxpay\ConfigurationExtension;

require __DIR__.'/../vendor/autoload.php';

if (!function_exists('dump')) {
    function dump(mixed ...$vars): void
    {
        var_dump($vars);
    }
}

$configFile = __DIR__.'/_config.php';
if (!is_file($configFile)) {
    exit('请复制 _config.php.dist 为 _config.php 并填写参数！');
}

$configuration = new Configuration(require $configFile);
$configurationExtension = new ConfigurationExtension($configuration);
