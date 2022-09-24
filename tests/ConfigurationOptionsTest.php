<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wxpay\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wxpay\Configuration;
use Siganushka\ApiClient\Wxpay\ConfigurationOptions;
use Siganushka\ApiClient\Wxpay\ParameterUtils;
use Siganushka\ApiClient\Wxpay\Query;
use Siganushka\ApiClient\Wxpay\Refund;
use Siganushka\ApiClient\Wxpay\SignatureUtils;
use Siganushka\ApiClient\Wxpay\Transfer;
use Siganushka\ApiClient\Wxpay\Unifiedorder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationOptionsTest extends TestCase
{
    public function testConfigure(): void
    {
        $resolver = new OptionsResolver();

        $configurationOptions = static::create();
        $configurationOptions->configure($resolver);

        static::assertSame([
            'appid',
            'mchid',
            'mchkey',
            'mch_client_cert',
            'mch_client_key',
        ], $resolver->getDefinedOptions());

        static::assertSame([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
        ], $resolver->resolve());

        static::assertSame([
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'appid' => 'foo',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
        ], $resolver->resolve([
            'appid' => 'foo',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
        ]));
    }

    public function testGetExtendedClasses(): void
    {
        $configurationOptions = static::create();

        static::assertSame([
            Query::class,
            Refund::class,
            Transfer::class,
            ParameterUtils::class,
            SignatureUtils::class,
            Unifiedorder::class,
        ], $configurationOptions::getExtendedClasses());
    }

    public static function create(Configuration $configuration = null): ConfigurationOptions
    {
        if (null === $configuration) {
            $configuration = ConfigurationTest::create();
        }

        return new ConfigurationOptions($configuration);
    }
}
