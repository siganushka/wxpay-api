<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\AbstractConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Configuration extends AbstractConfiguration
{
    public static function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::appid($resolver);
        OptionSet::mchid($resolver);
        OptionSet::mchkey($resolver);
        OptionSet::mch_client_cert($resolver);
        OptionSet::mch_client_key($resolver);

        foreach (['mch_client_cert', 'mch_client_key'] as $option) {
            $resolver->setDefault($option, null);
            $resolver->addAllowedTypes($option, 'null');
        }
    }
}
