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

    public function testJsapi(): void
    {
        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'prepay_id' => 'test_prepay_id',
        ];

        $data = $this->parameterUtils->jsapi($options);
        static::assertSame('test_appid', $data['appId']);
        static::assertSame('prepay_id=test_prepay_id', $data['package']);
        static::assertSame('MD5', $data['signType']);
        static::assertArrayHasKey('timeStamp', $data);
        static::assertArrayHasKey('nonceStr', $data);
        static::assertArrayHasKey('paySign', $data);

        $sign = $data['paySign'];
        unset($data['paySign']);

        static::assertTrue($this->signatureUtils->verify($sign, $data, [
            'mchkey' => $options['mchkey'],
        ]));
    }

    public function testApp(): void
    {
        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'prepay_id' => 'test_prepay_id',
        ];

        $data = $this->parameterUtils->app($options);
        static::assertSame('test_appid', $data['appid']);
        static::assertSame('test_mchid', $data['partnerid']);
        static::assertSame('test_prepay_id', $data['prepayid']);
        static::assertSame('Sign=WXPay', $data['package']);
        static::assertArrayHasKey('noncestr', $data);
        static::assertArrayHasKey('timestamp', $data);
        static::assertArrayHasKey('sign', $data);

        $sign = $data['sign'];
        unset($data['sign']);

        static::assertTrue($this->signatureUtils->verify($sign, $data, [
            'mchkey' => $options['mchkey'],
        ]));
    }
}
