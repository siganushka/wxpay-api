<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\AbstractConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wxpay configuration.
 */
class Configuration extends AbstractConfiguration
{
    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::appid($resolver);
        OptionsUtils::mchid($resolver);
        OptionsUtils::mchkey($resolver);
        OptionsUtils::mch_client_cert($resolver);
        OptionsUtils::mch_client_key($resolver);

        foreach (['mch_client_cert', 'mch_client_key'] as $option) {
            $resolver->setDefault($option, null);
            $resolver->addAllowedTypes($option, 'null');
        }
    }
}
