<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wxpay\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wxpay\SignatureUtils;
use Siganushka\ApiClient\Wxpay\Unifiedorder;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class UnifiedorderTest extends TestCase
{
    protected ?Unifiedorder $request = null;
    protected ?EncoderInterface $encoder = null;
    protected ?DecoderInterface $decoder = null;

    protected function setUp(): void
    {
        $this->encoder = new XmlEncoder();
        $this->decoder = new XmlEncoder();
        $this->request = new Unifiedorder(null, $this->encoder, $this->decoder);
    }

    protected function tearDown(): void
    {
        $this->encoder = null;
        $this->decoder = null;
        $this->request = null;
    }

    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();
        $this->request->configure($resolver);

        static::assertSame([
            'appid',
            'mchid',
            'mchkey',
            'sign_type',
            'noncestr',
            'client_ip',
            'using_slave_url',
            'device_info',
            'body',
            'detail',
            'attach',
            'out_trade_no',
            'fee_type',
            'total_fee',
            'time_start',
            'time_expire',
            'goods_tag',
            'notify_url',
            'trade_type',
            'product_id',
            'limit_pay',
            'openid',
            'receipt',
            'profit_sharing',
            'scene_info',
        ], $resolver->getDefinedOptions());

        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'noncestr' => 'test_noncestr',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ];

        static::assertEquals([
            'appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'mchkey' => $options['mchkey'],
            'sign_type' => 'MD5',
            'noncestr' => $options['noncestr'],
            'client_ip' => '0.0.0.0',
            'using_slave_url' => false,
            'device_info' => null,
            'body' => $options['body'],
            'detail' => null,
            'attach' => null,
            'out_trade_no' => $options['out_trade_no'],
            'fee_type' => null,
            'total_fee' => 1,
            'time_start' => null,
            'time_expire' => null,
            'goods_tag' => null,
            'notify_url' => $options['notify_url'],
            'trade_type' => $options['trade_type'],
            'product_id' => null,
            'limit_pay' => null,
            'openid' => $options['openid'],
            'receipt' => null,
            'profit_sharing' => null,
            'scene_info' => null,
        ], $resolver->resolve($options));

        $timeStartAt = new \DateTimeImmutable();
        $timeExpireAt = $timeStartAt->modify('+7 days');
        $options = array_merge($options, [
            'sign_type' => 'HMAC-SHA256',
            'client_ip' => '127.0.0.1',
            'using_slave_url' => true,
            'device_info' => 'test_device_info',
            'detail' => 'test_detail',
            'attach' => 'test_attach',
            'fee_type' => 'CNY',
            'time_start' => $timeStartAt,
            'time_expire' => $timeExpireAt,
            'goods_tag' => 'test_goods_tag',
            'product_id' => 'test_product_id',
            'limit_pay' => 'no_credit',
            'receipt' => 'Y',
            'profit_sharing' => 'Y',
            'scene_info' => 'test_scene_info',
        ]);

        static::assertEquals([
            'appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'mchkey' => $options['mchkey'],
            'sign_type' => $options['sign_type'],
            'noncestr' => $options['noncestr'],
            'client_ip' => $options['client_ip'],
            'using_slave_url' => $options['using_slave_url'],
            'device_info' => $options['device_info'],
            'body' => $options['body'],
            'detail' => $options['detail'],
            'attach' => $options['attach'],
            'out_trade_no' => $options['out_trade_no'],
            'fee_type' => $options['fee_type'],
            'total_fee' => $options['total_fee'],
            'time_start' => $timeStartAt->format('YmdHis'),
            'time_expire' => $timeExpireAt->format('YmdHis'),
            'goods_tag' => $options['goods_tag'],
            'notify_url' => $options['notify_url'],
            'trade_type' => $options['trade_type'],
            'product_id' => $options['product_id'],
            'limit_pay' => $options['limit_pay'],
            'openid' => $options['openid'],
            'receipt' => $options['receipt'],
            'profit_sharing' => $options['profit_sharing'],
            'scene_info' => $options['scene_info'],
        ], $resolver->resolve($options));
    }

    public function testBuild(): void
    {
        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'noncestr' => 'test_noncestr',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ];

        $requestOptions = $this->request->build($options);
        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Unifiedorder::URL, $requestOptions->getUrl());

        $body = $this->decoder->decode($requestOptions->toArray()['body'], 'xml');

        $signature = $body['sign'];
        unset($body['sign']);

        $signatureUtils = SignatureUtils::create();
        static::assertSame($signature, $signatureUtils->generate([
            'mchkey' => $options['mchkey'],
            'data' => $body,
        ]));

        static::assertSame([
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'nonce_str' => $options['noncestr'],
            'sign_type' => 'MD5',
            'body' => $options['body'],
            'out_trade_no' => $options['out_trade_no'],
            'total_fee' => (string) $options['total_fee'],
            'spbill_create_ip' => '0.0.0.0',
            'notify_url' => $options['notify_url'],
            'trade_type' => $options['trade_type'],
            'openid' => $options['openid'],
        ], $body);

        $timeStartAt = new \DateTimeImmutable();
        $timeExpireAt = $timeStartAt->modify('+7 days');
        $options = array_merge($options, [
            'sign_type' => 'HMAC-SHA256',
            'client_ip' => '127.0.0.1',
            'using_slave_url' => true,
            'device_info' => 'test_device_info',
            'detail' => 'test_detail',
            'attach' => 'test_attach',
            'fee_type' => 'CNY',
            'time_start' => $timeStartAt,
            'time_expire' => $timeExpireAt,
            'goods_tag' => 'test_goods_tag',
            'product_id' => 'test_product_id',
            'limit_pay' => 'no_credit',
            'receipt' => 'Y',
            'profit_sharing' => 'Y',
            'scene_info' => 'test_scene_info',
        ]);

        $requestOptions = $this->request->build($options);

        $body = $this->decoder->decode($requestOptions->toArray()['body'], 'xml');

        $signature = $body['sign'];
        unset($body['sign']);

        static::assertSame($signature, $signatureUtils->generate([
            'mchkey' => $options['mchkey'],
            'sign_type' => $options['sign_type'],
            'data' => $body,
        ]));

        static::assertSame([
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'device_info' => $options['device_info'],
            'nonce_str' => $options['noncestr'],
            'sign_type' => $options['sign_type'],
            'body' => $options['body'],
            'detail' => $options['detail'],
            'attach' => $options['attach'],
            'out_trade_no' => $options['out_trade_no'],
            'fee_type' => $options['fee_type'],
            'total_fee' => (string) $options['total_fee'],
            'spbill_create_ip' => $options['client_ip'],
            'time_start' => $timeStartAt->format('YmdHis'),
            'time_expire' => $timeExpireAt->format('YmdHis'),
            'goods_tag' => $options['goods_tag'],
            'notify_url' => $options['notify_url'],
            'trade_type' => $options['trade_type'],
            'product_id' => $options['product_id'],
            'limit_pay' => $options['limit_pay'],
            'openid' => $options['openid'],
            'receipt' => $options['receipt'],
            'profit_sharing' => $options['profit_sharing'],
            'scene_info' => $options['scene_info'],
        ], $body);
    }

    public function testSend(): void
    {
        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ];

        $data = [
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
        ];

        $body = $this->encoder->encode($data, 'xml');

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $result = (new Unifiedorder($client))->send($options);
        static::assertSame($data, $result);
    }

    public function testSendWithParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test_return_msg');

        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ];

        $data = [
            'return_code' => 'FAIL',
            'return_msg' => 'test_return_msg',
        ];

        $body = $this->encoder->encode($data, 'xml');

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        (new Unifiedorder($client))->send($options);
    }

    public function testResultCodeParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test_err_code_des');

        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ];

        $data = [
            'result_code' => 'FAIL',
            'err_code_des' => 'test_err_code_des',
        ];

        $body = $this->encoder->encode($data, 'xml');

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        (new Unifiedorder($client))->send($options);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        $this->request->build([
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ]);
    }

    public function testMchidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "mchid" is missing');

        $this->request->build([
            'appid' => 'test_appid',
            'mchkey' => 'test_mchkey',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ]);
    }

    public function testMchkeyMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "mchkey" is missing');

        $this->request->build([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'body' => 'test_body',
            'notify_url' => 'test_notify_url',
            'out_trade_no' => 'test_out_trade_no',
            'total_fee' => 1,
            'trade_type' => 'JSAPI',
            'openid' => 'test_openid',
        ]);
    }
}
