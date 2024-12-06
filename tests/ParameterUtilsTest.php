<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wxpay\ParameterUtils;
use Siganushka\ApiFactory\Wxpay\SignatureUtils;

class ParameterUtilsTest extends TestCase
{
    protected SignatureUtils $signatureUtils;
    protected ParameterUtils $parameterUtils;

    protected function setUp(): void
    {
        $this->signatureUtils = new SignatureUtils();
        $this->parameterUtils = new ParameterUtils($this->signatureUtils);
    }

    public function testResolve(): void
    {
        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'prepay_id' => 'test_prepay_id',
        ];

        $resolved = $this->parameterUtils->resolve($options);
        static::assertSame('test_appid', $resolved['appid']);
        static::assertSame('test_mchid', $resolved['mchid']);
        static::assertSame('test_mchkey', $resolved['mchkey']);
        static::assertSame('MD5', $resolved['sign_type']);
        static::assertArrayHasKey('timestamp', $resolved);
        static::assertArrayHasKey('noncestr', $resolved);
        static::assertSame('test_prepay_id', $resolved['prepay_id']);

        $resolved = $this->parameterUtils->resolve($options + [
            'sign_type' => 'HMAC-SHA256',
            'timestamp' => 'test_timestamp',
            'noncestr' => 'test_noncestr',
        ]);

        static::assertSame('test_appid', $resolved['appid']);
        static::assertSame('test_mchid', $resolved['mchid']);
        static::assertSame('test_mchkey', $resolved['mchkey']);
        static::assertSame('HMAC-SHA256', $resolved['sign_type']);
        static::assertSame('test_timestamp', $resolved['timestamp']);
        static::assertSame('test_noncestr', $resolved['noncestr']);
        static::assertSame('test_prepay_id', $resolved['prepay_id']);
    }

    public function testJsapiParameter(): void
    {
        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'prepay_id' => 'test_prepay_id',
        ];

        $parameter = $this->parameterUtils->jsapi($options);
        static::assertSame('test_appid', $parameter['appId']);
        static::assertSame('prepay_id=test_prepay_id', $parameter['package']);
        static::assertSame('MD5', $parameter['signType']);
        static::assertArrayHasKey('timeStamp', $parameter);
        static::assertArrayHasKey('nonceStr', $parameter);
        static::assertArrayHasKey('paySign', $parameter);

        $sign = $parameter['paySign'];
        unset($parameter['paySign']);

        static::assertTrue($this->signatureUtils->verify($sign, [
            'mchkey' => $options['mchkey'],
            'data' => $parameter,
        ]));
    }

    public function testAppParameter(): void
    {
        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'prepay_id' => 'test_prepay_id',
        ];

        $parameter = $this->parameterUtils->app($options);
        static::assertSame('test_appid', $parameter['appid']);
        static::assertSame('test_mchid', $parameter['partnerid']);
        static::assertSame('test_prepay_id', $parameter['prepayid']);
        static::assertSame('Sign=WXPay', $parameter['package']);
        static::assertArrayHasKey('noncestr', $parameter);
        static::assertArrayHasKey('timestamp', $parameter);
        static::assertArrayHasKey('sign', $parameter);

        $sign = $parameter['sign'];
        unset($parameter['sign']);

        static::assertTrue($this->signatureUtils->verify($sign, [
            'mchkey' => $options['mchkey'],
            'data' => $parameter,
        ]));
    }
}
