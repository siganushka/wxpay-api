<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wxpay\SignatureUtils;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class SignatureUtilsTest extends TestCase
{
    protected SignatureUtils $signatureUtils;

    protected function setUp(): void
    {
        $this->signatureUtils = new SignatureUtils();
    }

    public function testResolve(): void
    {
        static::assertEquals([
            'sign_type' => 'MD5',
            'mchkey' => 'foo',
        ], $this->signatureUtils->resolve([
            'mchkey' => 'foo',
        ]));

        static::assertEquals([
            'sign_type' => 'HMAC-SHA256',
            'mchkey' => 'foo',
        ], $this->signatureUtils->resolve([
            'mchkey' => 'foo',
            'sign_type' => 'HMAC-SHA256',
        ]));
    }

    /**
     * @dataProvider getSignatureProvider
     */
    public function testGenerate(string $key, array $data, string $signType): void
    {
        $options = [
            'mchkey' => $key,
            'sign_type' => $signType,
        ];

        $signature = $this->signatureUtils->generate($data, $options);
        static::assertTrue($this->signatureUtils->verify($signature, $data, $options));
    }

    public function testMchkeyMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "mchkey" is missing');

        $this->signatureUtils->generate(['foo' => 'hello']);
    }

    public function getSignatureProvider(): array
    {
        return [
            [
                'foo_key',
                ['foo' => 'hello'],
                'MD5',
            ],
            [
                'bar_key',
                ['bar' => 'world'],
                'HMAC-SHA256',
            ],
            [
                'baz_key',
                ['bar' => 'hello world'],
                'MD5',
            ],
            [
                'c2dd2e64a672e5e1b82c019be848c2df',
                [
                    'return_code' => 'SUCCESS',
                    'return_msg' => 'OK',
                    'result_code' => 'SUCCESS',
                    'mch_id' => '1619665394',
                    'appid' => 'wx85bbb9f0e9460321',
                    'nonce_str' => 'iwMmaj4dS9slhxIH',
                    'prepay_id' => 'wx21170426533555ff1203597cc057e00000',
                    'trade_type' => 'JSAPI',
                ],
                'MD5',
            ],
        ];
    }
}
