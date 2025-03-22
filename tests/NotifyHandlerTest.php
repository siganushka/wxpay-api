<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wxpay\NotifyHandler;
use Siganushka\ApiFactory\Wxpay\SignatureUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class NotifyHandlerTest extends TestCase
{
    protected NotifyHandler $notifyHandler;
    protected SignatureUtils $signatureUtils;

    protected function setUp(): void
    {
        $this->notifyHandler = new NotifyHandler();
        $this->signatureUtils = new SignatureUtils();
    }

    public function testHandle(): void
    {
        $notifyData = [
            'foo' => 'bar',
        ];

        // Generate signature
        $notifyData['sign'] = $this->signatureUtils->generate($notifyData, ['mchkey' => 'foo']);

        $encoder = new XmlEncoder();
        $content = $encoder->encode($notifyData, 'xml');

        $request = Request::create('/', 'POST', [], [], [], [], $content);

        $data = $this->notifyHandler->handle($request, ['mchkey' => 'foo']);
        static::assertSame($notifyData, $data);
    }

    public function testSuccessResponse(): void
    {
        $encoder = new XmlEncoder();

        /** @var string */
        $content = $this->notifyHandler->success()->getContent();
        static::assertSame([
            'return_code' => 'SUCCESS',
        ], $encoder->decode($content, 'xml'));

        /** @var string */
        $content = $this->notifyHandler->success('foo')->getContent();
        static::assertSame([
            'return_code' => 'SUCCESS',
            'return_msg' => 'foo',
        ], $encoder->decode($content, 'xml'));
    }

    public function testFailResponse(): void
    {
        $encoder = new XmlEncoder();

        /** @var string */
        $content = $this->notifyHandler->fail()->getContent();
        static::assertSame([
            'return_code' => 'FAIL',
        ], $encoder->decode($content, 'xml'));

        /** @var string */
        $content = $this->notifyHandler->fail('foo')->getContent();
        static::assertSame([
            'return_code' => 'FAIL',
            'return_msg' => 'foo',
        ], $encoder->decode($content, 'xml'));
    }

    public function testHandleWithInvalidSignatureException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid signature');

        $notifyData = [
            'foo' => 'bar',
            'sign' => 'invalid_sign',
        ];

        $encoder = new XmlEncoder();
        $content = $encoder->encode($notifyData, 'xml');

        $request = Request::create('/', 'POST', [], [], [], [], $content);

        $data = $this->notifyHandler->handle($request, ['mchkey' => 'foo']);
        static::assertSame($notifyData, $data);
    }

    public function testHandleWithEmptyContentException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid Request');

        $request = Request::create('/');

        $this->notifyHandler->handle($request);
    }
}
