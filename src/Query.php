<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\RequestOptions;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_2
 */
class Query extends AbstractWxpayRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/pay/orderquery';
    public const URL2 = 'https://api2.mch.weixin.qq.com/pay/orderquery';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::appid($resolver);
        OptionSet::mchid($resolver);
        OptionSet::mchkey($resolver);
        OptionSet::sign_type($resolver);
        OptionSet::noncestr($resolver);
        OptionSet::using_slave_url($resolver);

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
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $body = array_filter([
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'transaction_id' => $options['transaction_id'],
            'out_trade_no' => $options['out_trade_no'],
            'nonce_str' => $options['noncestr'],
            'sign_type' => $options['sign_type'],
        ], fn ($value) => null !== $value);

        // Generate signature
        $body['sign'] = $this->signatureUtils->generate($body, [
            'mchkey' => $options['mchkey'],
            'sign_type' => $options['sign_type'],
        ]);

        $request
            ->setMethod('POST')
            ->setUrl($options['using_slave_url'] ? static::URL2 : static::URL)
            ->setBody($this->serializer->serialize($body, 'xml'))
        ;
    }
}
