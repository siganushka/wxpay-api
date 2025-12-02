<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\AbstractConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractConfiguration<array{
 *  appid: string,
 *  mchid: string,
 *  mchkey: string,
 *  mch_client_cert: string|null,
 *  mch_client_key: string|null
 * }>
 */
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
