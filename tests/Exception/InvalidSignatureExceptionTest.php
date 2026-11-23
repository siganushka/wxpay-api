<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wxpay\Exception\InvalidSignatureException;

class InvalidSignatureExceptionTest extends TestCase
{
    public function testAll(): void
    {
        $exception = new InvalidSignatureException('test_signature', ['foo' => 'bar']);

        static::assertSame('Invalid signature.', $exception->getMessage());
        static::assertSame('test_signature', $exception->getSignature());
        static::assertSame(['foo' => 'bar'], $exception->getData());
    }
}
