<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wxpay\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wxpay\GenericUtils;

class GenericUtilsTest extends TestCase
{
    public function testAll(): void
    {
        static::assertIsString(GenericUtils::getTimestamp());
        static::assertIsString(GenericUtils::getNonceStr());
        static::assertSame('0.0.0.0', GenericUtils::getClientIp());
        static::assertSame('http://localhost', GenericUtils::getCurrentUrl());
    }
}
