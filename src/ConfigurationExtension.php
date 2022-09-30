<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\ResolverExtensionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationExtension implements ResolverExtensionInterface
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        foreach ($this->configuration as $key => $value) {
            if (null !== $value) {
                $resolver->setDefault($key, $value);
            }
        }
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
