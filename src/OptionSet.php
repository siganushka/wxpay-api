<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionConfigurator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OptionSet
{
    public const SIGN_TYPE_SHA256 = 'HMAC-SHA256';
    public const SIGN_TYPE_MD5 = 'MD5';

    public static function appid(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('appid')
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function mchid(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('mchid')
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function mchkey(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('mchkey')
            ->required()
            ->allowedTypes('string')
        ;
    }

    public static function mch_client_cert(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('mch_client_cert')
            ->required()
            ->allowedTypes('string')
            ->normalize(function (Options $options, ?string $mchClientCert) {
                if (null !== $mchClientCert && !is_file($mchClientCert)) {
                    throw new InvalidOptionsException(sprintf('The option "mch_client_cert" with file "%s" does not exists.', $mchClientCert));
                }

                return $mchClientCert;
            })
        ;
    }

    public static function mch_client_key(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('mch_client_key')
            ->required()
            ->allowedTypes('string')
            ->normalize(function (Options $options, ?string $mchClientKey) {
                if (null !== $mchClientKey && !is_file($mchClientKey)) {
                    throw new InvalidOptionsException(sprintf('The option "mch_client_key" with file "%s" does not exists.', $mchClientKey));
                }

                return $mchClientKey;
            })
        ;
    }

    public static function sign_type(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('sign_type')
            ->default(static::SIGN_TYPE_MD5)
            ->allowedValues(static::SIGN_TYPE_MD5, static::SIGN_TYPE_SHA256)
        ;
    }

    public static function timestamp(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('timestamp')
            ->default((string) time())
            ->allowedTypes('string')
        ;
    }

    public static function noncestr(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('noncestr')
            ->default(bin2hex(random_bytes(16)))
            ->allowedTypes('string')
        ;
    }

    public static function client_ip(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('client_ip')
            ->default($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'))
            ->allowedTypes('string')
        ;
    }

    public static function using_slave_url(OptionsResolver $resolver): OptionConfigurator
    {
        return $resolver
            ->define('using_slave_url')
            ->default(false)
            ->allowedTypes('bool')
        ;
    }
}
