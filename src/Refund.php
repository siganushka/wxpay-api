<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\RequestOptions;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_4
 */
class Refund extends AbstractWxpayRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::appid($resolver);
        OptionSet::mchid($resolver);
        OptionSet::mchkey($resolver);
        OptionSet::mch_client_cert($resolver);
        OptionSet::mch_client_key($resolver);
        OptionSet::sign_type($resolver);
        OptionSet::noncestr($resolver);

        $resolver
            ->define('transaction_id')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('out_trade_no')
            ->default(null)
            ->allowedTypes('null', 'string')
            ->normalize(function (Options $options, ?string $outTradeNo) {
                if (null === $options['transaction_id'] && null === $outTradeNo) {
                    throw new MissingOptionsException('The required option "transaction_id" or "out_trade_no" is missing.');
                }

                return $outTradeNo;
            })
        ;

        $resolver
            ->define('out_refund_no')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('total_fee')
            ->required()
            ->allowedTypes('int')
        ;

        $resolver
            ->define('refund_fee')
            ->required()
            ->allowedTypes('int')
        ;

        $resolver
            ->define('refund_fee_type')
            ->default(null)
            ->allowedValues(null, 'CNY')
        ;

        $resolver
            ->define('refund_desc')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('refund_account')
            ->default(null)
            ->allowedValues(null, 'REFUND_SOURCE_UNSETTLED_FUNDS', 'REFUND_SOURCE_RECHARGE_FUNDS')
        ;

        $resolver
            ->define('notify_url')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $body = array_filter([
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'sign_type' => $options['sign_type'],
            'nonce_str' => $options['noncestr'],
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => $options['out_trade_no'],
            'out_refund_no' => $options['out_refund_no'],
            'total_fee' => $options['total_fee'],
            'refund_fee' => $options['refund_fee'],
            'refund_fee_type' => $options['refund_fee_type'],
            'refund_desc' => $options['refund_desc'],
            'refund_account' => $options['refund_account'],
            'notify_url' => $options['notify_url'],
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
