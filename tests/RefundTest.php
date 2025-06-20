<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\Wxpay\Refund;
use Siganushka\ApiFactory\Wxpay\SignatureUtils;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class RefundTest extends TestCase
{
    protected SerializerInterface $serializer;
    protected SignatureUtils $signatureUtils;
    protected Refund $request;

    protected function setUp(): void
    {
        $this->serializer = new Serializer([new ArrayDenormalizer()], [new XmlEncoder()]);
        $this->signatureUtils = new SignatureUtils();
        $this->request = new Refund(null, $this->serializer, $this->signatureUtils);
    }

    public function testResolve(): void
    {
        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
            'noncestr' => 'test_noncestr',
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        static::assertEquals([
            'appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'mchkey' => $options['mchkey'],
            'mch_client_cert' => $options['mch_client_cert'],
            'mch_client_key' => $options['mch_client_key'],
            'sign_type' => 'MD5',
            'out_refund_no' => $options['out_refund_no'],
            'total_fee' => $options['total_fee'],
            'refund_fee' => $options['refund_fee'],
            'noncestr' => $options['noncestr'],
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => null,
            'refund_fee_type' => null,
            'refund_desc' => null,
            'refund_account' => null,
            'notify_url' => null,
        ], $this->request->resolve($options));

        static::assertEquals([
            'appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'mchkey' => $options['mchkey'],
            'mch_client_cert' => $options['mch_client_cert'],
            'mch_client_key' => $options['mch_client_key'],
            'sign_type' => 'HMAC-SHA256',
            'out_refund_no' => $options['out_refund_no'],
            'total_fee' => $options['total_fee'],
            'refund_fee' => $options['refund_fee'],
            'noncestr' => $options['noncestr'],
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => 'test_out_trade_no',
            'refund_fee_type' => 'CNY',
            'refund_desc' => 'test_refund_desc',
            'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
            'notify_url' => 'test_notify_url',
        ], $this->request->resolve($options + [
            'sign_type' => 'HMAC-SHA256',
            'out_trade_no' => 'test_out_trade_no',
            'refund_fee_type' => 'CNY',
            'refund_desc' => 'test_refund_desc',
            'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
            'notify_url' => 'test_notify_url',
        ]));
    }

    public function testBuild(): void
    {
        $configuration = ConfigurationTest::create();

        $options = [
            'appid' => $configuration['appid'],
            'mchid' => $configuration['mchid'],
            'mchkey' => $configuration['mchkey'],
            'mch_client_cert' => $configuration['mch_client_cert'],
            'mch_client_key' => $configuration['mch_client_key'],
            'noncestr' => uniqid(),
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        $requestOptions = $this->request->build($options);
        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame('https://api.mch.weixin.qq.com/secapi/pay/refund', $requestOptions->getUrl());
        static::assertSame($requestOptions->toArray()['local_cert'], $configuration['mch_client_cert']);
        static::assertSame($requestOptions->toArray()['local_pk'], $configuration['mch_client_key']);

        /** @var array{ sign: string } */
        $body = $this->serializer->deserialize($requestOptions->toArray()['body'], 'string[]', 'xml');

        $signature = $body['sign'];
        unset($body['sign']);

        static::assertSame($signature, $this->signatureUtils->generate($body, [
            'mchkey' => $options['mchkey'],
        ]));

        static::assertEquals([
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'sign_type' => 'MD5',
            'nonce_str' => $options['noncestr'],
            'transaction_id' => $options['transaction_id'],
            'out_refund_no' => $options['out_refund_no'],
            'total_fee' => (string) $options['total_fee'],
            'refund_fee' => (string) $options['refund_fee'],
        ], $body);

        $requestOptions = $this->request->build($options + [
            'sign_type' => 'HMAC-SHA256',
            'out_trade_no' => 'test_out_trade_no',
            'refund_fee_type' => 'CNY',
            'refund_desc' => 'test_refund_desc',
            'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
            'notify_url' => 'test_notify_url',
        ]);

        /** @var array{ sign: string } */
        $body = $this->serializer->deserialize($requestOptions->toArray()['body'], 'string[]', 'xml');

        $signature = $body['sign'];
        unset($body['sign']);

        static::assertSame($signature, $this->signatureUtils->generate($body, [
            'mchkey' => $options['mchkey'],
            'sign_type' => 'HMAC-SHA256',
        ]));

        static::assertEquals([
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'sign_type' => 'HMAC-SHA256',
            'nonce_str' => $options['noncestr'],
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => 'test_out_trade_no',
            'out_refund_no' => $options['out_refund_no'],
            'total_fee' => (string) $options['total_fee'],
            'refund_fee' => (string) $options['refund_fee'],
            'refund_fee_type' => 'CNY',
            'refund_desc' => 'test_refund_desc',
            'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
            'notify_url' => 'test_notify_url',
        ], $body);
    }

    public function testSend(): void
    {
        $configuration = ConfigurationTest::create();

        $options = [
            'appid' => $configuration['appid'],
            'mchid' => $configuration['mchid'],
            'mchkey' => $configuration['mchkey'],
            'mch_client_cert' => $configuration['mch_client_cert'],
            'mch_client_key' => $configuration['mch_client_key'],
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        $data = [
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
        ];

        $body = $this->serializer->serialize($data, 'xml');

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $result = (new Refund($client))->send($options);
        static::assertSame($data, $result);
    }

    public function testSendWithParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test_return_msg');

        $configuration = ConfigurationTest::create();

        $options = [
            'appid' => $configuration['appid'],
            'mchid' => $configuration['mchid'],
            'mchkey' => $configuration['mchkey'],
            'mch_client_cert' => $configuration['mch_client_cert'],
            'mch_client_key' => $configuration['mch_client_key'],
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        $data = [
            'return_code' => 'FAIL',
            'return_msg' => 'test_return_msg',
        ];

        $body = $this->serializer->serialize($data, 'xml');

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        (new Refund($client))->send($options);
    }

    public function testResultCodeParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test_err_code_des');

        $configuration = ConfigurationTest::create();

        $options = [
            'appid' => $configuration['appid'],
            'mchid' => $configuration['mchid'],
            'mchkey' => $configuration['mchkey'],
            'mch_client_cert' => $configuration['mch_client_cert'],
            'mch_client_key' => $configuration['mch_client_key'],
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ];

        $data = [
            'result_code' => 'FAIL',
            'err_code_des' => 'test_err_code_des',
        ];

        $body = $this->serializer->serialize($data, 'xml');

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        (new Refund($client))->send($options);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        $this->request->build([
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
            'noncestr' => 'test_noncestr',
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ]);
    }

    public function testMchidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "mchid" is missing');

        $this->request->build([
            'appid' => 'test_appid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
            'noncestr' => 'test_noncestr',
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ]);
    }

    public function testMchkeyMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "mchkey" is missing');

        $this->request->build([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
            'noncestr' => 'test_noncestr',
            'transaction_id' => 'test_transaction_id',
            'out_refund_no' => 'test_out_refund_no',
            'total_fee' => 12,
            'refund_fee' => 10,
        ]);
    }
}
