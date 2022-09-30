<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wxpay\ConfigurationExtension;
use Siganushka\ApiFactory\Wxpay\ParameterUtils;
use Siganushka\ApiFactory\Wxpay\Query;
use Siganushka\ApiFactory\Wxpay\Refund;
use Siganushka\ApiFactory\Wxpay\SignatureUtils;
use Siganushka\ApiFactory\Wxpay\Transfer;
use Siganushka\ApiFactory\Wxpay\Unifiedorder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationExtensionTest extends TestCase
{
    protected ?ConfigurationExtension $extension = null;

    protected function setUp(): void
    {
        $this->extension = new ConfigurationExtension(ConfigurationTest::create());
    }

    protected function tearDown(): void
    {
        $this->extension = null;
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->extension->configureOptions($resolver);

        static::assertEquals([
            'appid' => 'test_appid',
            'mchid' => 'test_mchid',
            'mchkey' => 'test_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
        ], $resolver->resolve());

        static::assertEquals([
            'appid' => 'foo',
            'mchid' => 'foo_mchid',
            'mchkey' => 'foo_mchkey',
            'mch_client_cert' => ConfigurationTest::MCH_CLIENT_CERT,
            'mch_client_key' => ConfigurationTest::MCH_CLIENT_KEY,
        ], $resolver->resolve([
            'appid' => 'foo',
            'mchid' => 'foo_mchid',
            'mchkey' => 'foo_mchkey',
        ]));
    }

    public function testGetExtendedClasses(): void
    {
        static::assertEquals([
            Query::class,
            Refund::class,
            Transfer::class,
            Unifiedorder::class,
            ParameterUtils::class,
            SignatureUtils::class,
        ], ConfigurationExtension::getExtendedClasses());
    }
}
