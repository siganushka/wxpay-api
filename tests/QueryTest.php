<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\Wxpay\Query;
use Siganushka\ApiFactory\Wxpay\SignatureUtils;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class QueryTest extends TestCase
{
    protected SerializerInterface $serializer;
    protected SignatureUtils $signatureUtils;
    protected Query $request;

    protected function setUp(): void
    {
        $this->serializer = new Serializer([new ArrayDenormalizer()], [new XmlEncoder()]);
        $this->signatureUtils = new SignatureUtils();
        $this->request = new Query(null, $this->serializer, $this->signatureUtils);
    }

    public function testResolve(): void
    {
        $options = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'transaction_id' => 'test_transaction_id',
            'noncestr' => 'test_noncestr',
        ];

        static::assertEquals([
            'appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'mchkey' => $options['mchkey'],
            'sign_type' => 'MD5',
            'noncestr' => $options['noncestr'],
            'using_slave_url' => false,
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => null,
        ], $this->request->resolve($options));

        static::assertEquals([
            'appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'mchkey' => $options['mchkey'],
            'sign_type' => 'HMAC-SHA256',
            'noncestr' => $options['noncestr'],
            'using_slave_url' => true,
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => 'test_out_trade_no',
        ], $this->request->resolve($options + [
            'sign_type' => 'HMAC-SHA256',
            'using_slave_url' => true,
            'out_trade_no' => 'test_out_trade_no',
        ]));
    }

    public function testBuild(): void
    {
        $options = [
            'appid' => 'foo',
            'mchid' => 'bar',
            'mchkey' => 'test_mchkey',
            'noncestr' => uniqid(),
            'transaction_id' => 'test_transaction_id',
        ];

        $requestOptions = $this->request->build($options);
        static::assertSame('POST', $requestOptions->getMethod());
        static::assertSame(Query::URL, $requestOptions->getUrl());

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
            'transaction_id' => $options['transaction_id'],
            'nonce_str' => $options['noncestr'],
        ], $body);

        $requestOptions = $this->request->build($options + [
            'sign_type' => 'HMAC-SHA256',
            'out_trade_no' => 'test_out_trade_no',
            'using_slave_url' => true,
        ]);

        static::assertSame(Query::URL2, $requestOptions->getUrl());

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
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => 'test_out_trade_no',
            'nonce_str' => $options['noncestr'],
        ], $body);
    }

    public function testSend(): void
    {
        $options = [
            'appid' => 'foo',
            'mchid' => 'bar',
            'mchkey' => 'test_mchkey',
            'transaction_id' => 'test_transaction_id',
        ];

        $data = [
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
        ];

        $body = $this->serializer->serialize($data, 'xml');

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        $result = (new Query($client))->send($options);
        static::assertSame($data, $result);
    }

    public function testSendWithParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test_return_msg');

        $options = [
            'appid' => 'foo',
            'mchid' => 'bar',
            'mchkey' => 'test_mchkey',
            'transaction_id' => 'test_transaction_id',
        ];

        $data = [
            'return_code' => 'FAIL',
            'return_msg' => 'test_return_msg',
        ];

        $body = $this->serializer->serialize($data, 'xml');

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        (new Query($client))->send($options);
    }

    public function testResultCodeParseResponseException(): void
    {
        $this->expectException(ParseResponseException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test_err_code_des');

        $options = [
            'appid' => 'foo',
            'mchid' => 'bar',
            'mchkey' => 'test_mchkey',
            'transaction_id' => 'test_transaction_id',
        ];

        $data = [
            'result_code' => 'FAIL',
            'err_code_des' => 'test_err_code_des',
        ];

        $body = $this->serializer->serialize($data, 'xml');

        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient($mockResponse);

        (new Query($client))->send($options);
    }

    public function testAppidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "appid" is missing');

        $this->request->build([
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'transaction_id' => 'test_transaction_id',
            'noncestr' => 'test_noncestr',
        ]);
    }

    public function testMchidMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "mchid" is missing');

        $this->request->build([
            'appid' => 'test_appid',
            'mchkey' => 'test_mchkey',
            'transaction_id' => 'test_transaction_id',
            'noncestr' => 'test_noncestr',
        ]);
    }

    public function testMchkeyMissingOptionsException(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "mchkey" is missing');

        $this->request->build([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'transaction_id' => 'test_transaction_id',
            'noncestr' => 'test_noncestr',
        ]);
    }
}
