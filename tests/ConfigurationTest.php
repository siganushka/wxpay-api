<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wxpay\Configuration;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class ConfigurationTest extends TestCase
{
    public const MCH_CLIENT_CERT = __DIR__.'/Fixtures/cert.pem';
    public const MCH_CLIENT_KEY = __DIR__.'/Fixtures/key.pem';

    public function testAll(): void
    {
        $configuration = static::create();

        static::assertInstanceOf(\Countable::class, $configuration);
        static::assertInstanceOf(\IteratorAggregate::class, $configuration);
        static::assertInstanceOf(\ArrayAccess::class, $configuration);
        static::assertSame(5, $configuration->count());

        static::assertEquals([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => static::MCH_CLIENT_CERT,
            'mch_client_key' => static::MCH_CLIENT_KEY,
        ], $configuration->toArray());

        $configuration = static::create([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
        ]);

        static::assertEquals([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => null,
            'mch_client_key' => null,
        ], $configuration->toArray());
    }

    public function testResolve(): void
    {
        $configs = [
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => static::MCH_CLIENT_CERT,
            'mch_client_key' => static::MCH_CLIENT_KEY,
        ];

        $configuration = static::create($configs);
        static::assertEquals($configuration->toArray(), $configuration->resolve($configs));
    }

    public function testAppidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "appid" with value 123 is expected to be of type "string", but is of type "int"');

        static::create([
            'appid' => 123,
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
        ]);
    }

    public function testMchidInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mchid" with value 123 is expected to be of type "string", but is of type "int"');

        static::create([
            'appid' => 'test_appid',
            'mchid' => 123,
            'mchkey' => 'test_mchkey',
        ]);
    }

    public function testMchkeyInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mchkey" with value 123 is expected to be of type "string", but is of type "int"');

        static::create([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 123,
        ]);
    }

    public function testMchClientCertInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mch_client_cert" with value 123 is expected to be of type "string" or "null", but is of type "int"');

        static::create([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => 123,
        ]);
    }

    public function testMchClientCertFileNotFoundException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mch_client_cert" file does not exists');

        static::create([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => 'non_existing_file.pem',
        ]);
    }

    public function testMchClientKeyInvalidOptionsException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mch_client_key" with value 123 is expected to be of type "string" or "null", but is of type "int"');

        static::create([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_key' => 123,
        ]);
    }

    public function testMchClientKeyFileNotFoundException(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "mch_client_key" file does not exists');

        static::create([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_key' => 'non_existing_file.pem',
        ]);
    }

    public static function create(array $configs = null): Configuration
    {
        if (null === $configs) {
            $configs = [
                'appid' => 'test_appid',
                'mchid' => 'test_mchid',
                'mchkey' => 'test_mchkey',
                'mch_client_cert' => static::MCH_CLIENT_CERT,
                'mch_client_key' => static::MCH_CLIENT_KEY,
            ];
        }

        return new Configuration($configs);
    }
}
