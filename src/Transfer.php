<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\RequestOptions;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
 */
class Transfer extends AbstractWxpayRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::appid($resolver);
        OptionSet::mchid($resolver);
        OptionSet::mchkey($resolver);
        OptionSet::mch_client_cert($resolver);
        OptionSet::mch_client_key($resolver);
        OptionSet::sign_type($resolver);
        OptionSet::noncestr($resolver);
        OptionSet::client_ip($resolver);

        $resolver
            ->define('device_info')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('partner_trade_no')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('openid')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('check_name')
            ->default('NO_CHECK')
            ->allowedValues('NO_CHECK', 'FORCE_CHECK')
        ;

        $resolver
            ->define('re_user_name')
            ->default(null)
            ->allowedTypes('null', 'string')
            ->normalize(function (Options $options, ?string $reUserName) {
                if ('FORCE_CHECK' === $options['check_name'] && null === $reUserName) {
                    throw new MissingOptionsException('The required option "re_user_name" is missing (when "check_name" option is set to "FORCE_CHECK").');
                }

                return $reUserName;
            })
        ;

        $resolver
            ->define('amount')
            ->required()
            ->allowedTypes('int')
        ;

        $resolver
            ->define('desc')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('scene')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('brand_id')
            ->default(null)
            ->allowedTypes('null', 'int')
        ;

        $resolver
            ->define('finder_template_id')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $body = array_filter([
            'mch_appid' => $options['appid'],
            'mchid' => $options['mchid'],
            'device_info' => $options['device_info'],
            'nonce_str' => $options['noncestr'],
            'partner_trade_no' => $options['partner_trade_no'],
            'openid' => $options['openid'],
            'check_name' => $options['check_name'],
            're_user_name' => $options['re_user_name'],
            'amount' => $options['amount'],
            'desc' => $options['desc'],
            'spbill_create_ip' => $options['client_ip'],
            'scene' => $options['scene'],
            'brand_id' => $options['brand_id'],
            'finder_template_id' => $options['finder_template_id'],
        ], fn ($value) => null !== $value);

        // Generate signature
        $body['sign'] = $this->signatureUtils->generate($body, [
            'mchkey' => $options['mchkey'],
            'sign_type' => $options['sign_type'],
        ]);

        $request
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setBody($this->serializer->serialize($body, 'xml'))
            ->setLocalCert($options['mch_client_cert'])
            ->setLocalPk($options['mch_client_key'])
        ;
    }
}
