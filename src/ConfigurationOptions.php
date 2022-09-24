<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wxpay;

use Siganushka\ApiClient\OptionsExtensionInterface;
use Siganushka\ApiClient\OptionsExtensionTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationOptions implements OptionsExtensionInterface
{
    use OptionsExtensionTrait;

    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        foreach ($this->configuration as $key => $value) {
            if (null !== $value) {
                $resolver->setDefault($key, $value);
            }
        }
    }

    public static function getExtendedClasses(): array
    {
        return [
            Query::class,
            Refund::class,
            Transfer::class,
            ParameterUtils::class,
            SignatureUtils::class,
            Unifiedorder::class,
        ];
    }
}
