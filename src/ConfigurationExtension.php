<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\ResolverExtensionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationExtension implements ResolverExtensionInterface
{
    public function __construct(private readonly Configuration $configuration)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $fn = fn ($value, string $key) => $resolver->isDefined($key) && null !== $value;

        $configs = $this->configuration->toArray();
        $resolver->setDefaults(array_filter($configs, $fn, \ARRAY_FILTER_USE_BOTH));
    }

    public static function getExtendedClasses(): iterable
    {
        return [
            Query::class,
            Refund::class,
            Transfer::class,
            Unifiedorder::class,
            ParameterUtils::class,
            SignatureUtils::class,
        ];
    }
}
