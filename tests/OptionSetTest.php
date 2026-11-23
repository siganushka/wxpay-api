<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wxpay\OptionSet;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OptionSetTest extends TestCase
{
    public function testClientIpFromForwardedFor(): void
    {
        $this->withServer(['HTTP_X_FORWARDED_FOR' => '1.2.3.4', 'REMOTE_ADDR' => '5.6.7.8'], function (): void {
            static::assertSame('1.2.3.4', $this->resolveClientIp());
        });
    }

    public function testClientIpFromRemoteAddr(): void
    {
        $this->withServer(['REMOTE_ADDR' => '5.6.7.8'], function (): void {
            static::assertSame('5.6.7.8', $this->resolveClientIp());
        });
    }

    public function testClientIpDefault(): void
    {
        $this->withServer([], function (): void {
            static::assertSame('0.0.0.0', $this->resolveClientIp());
        });
    }

    public function testClientIpWithExplicitValue(): void
    {
        static::assertSame('9.9.9.9', $this->resolveClientIp(['client_ip' => '9.9.9.9']));
    }

    public function testSignTypeDefault(): void
    {
        $resolver = new OptionsResolver();
        OptionSet::sign_type($resolver);

        static::assertSame(OptionSet::SIGN_TYPE_MD5, $resolver->resolve()['sign_type']);
    }

    /**
     * @param array<string, string> $vars
     */
    private function withServer(array $vars, \Closure $callback): void
    {
        $server = $_SERVER;

        unset($_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REMOTE_ADDR']);
        foreach ($vars as $key => $value) {
            $_SERVER[$key] = $value;
        }

        try {
            $callback();
        } finally {
            $_SERVER = $server;
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    private function resolveClientIp(array $options = []): string
    {
        $resolver = new OptionsResolver();
        OptionSet::client_ip($resolver);

        $resolved = $resolver->resolve($options);

        static::assertIsString($resolved['client_ip']);

        return $resolved['client_ip'];
    }
}
