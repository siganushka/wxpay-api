<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\RequestOptions;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1
 */
class Unifiedorder extends AbstractWxpayRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    public const URL2 = 'https://api2.mch.weixin.qq.com/pay/unifiedorder';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::appid($resolver);
        OptionSet::mchid($resolver);
        OptionSet::mchkey($resolver);
        OptionSet::sign_type($resolver);
        OptionSet::noncestr($resolver);
        OptionSet::client_ip($resolver);
        OptionSet::using_slave_url($resolver);

        $resolver
            ->define('device_info')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('body')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('detail')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('attach')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('out_trade_no')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('fee_type')
            ->default(null)
            ->allowedValues(null, 'CNY')
        ;

        $resolver
            ->define('total_fee')
            ->required()
            ->allowedTypes('int')
        ;

        $resolver
            ->define('time_start')
            ->default(null)
            ->allowedTypes('null', \DateTimeInterface::class)
            ->normalize(fn (Options $options, ?\DateTimeInterface $timeStart) => null === $timeStart ? null : $timeStart->format('YmdHis'))
        ;

        $resolver
            ->define('time_expire')
            ->default(null)
            ->allowedTypes('null', \DateTimeInterface::class)
            ->normalize(fn (Options $options, ?\DateTimeInterface $timeExpire) => null === $timeExpire ? null : $timeExpire->format('YmdHis'))
        ;

        $resolver
            ->define('goods_tag')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;

        $resolver
            ->define('notify_url')
            ->required()
            ->allowedTypes('string')
        ;

        $resolver
            ->define('trade_type')
            ->required()
            ->allowedValues('JSAPI', 'NATIVE', 'APP', 'MWEB')
        ;

        $resolver
            ->define('product_id')
            ->default(null)
            ->allowedTypes('null', 'string')
            ->normalize(function (Options $options, ?string $productId) {
                if ('NATIVE' === $options['trade_type'] && null === $productId) {
                    throw new MissingOptionsException('The required option "product_id" is missing (when "trade_type" option is set to "NATIVE").');
                }

                return $productId;
            })
        ;

        $resolver
            ->define('limit_pay')
            ->default(null)
            ->allowedValues(null, 'no_credit')
        ;

        $resolver
            ->define('openid')
            ->default(null)
            ->allowedTypes('null', 'string')
            ->normalize(function (Options $options, ?string $openid) {
                if ('JSAPI' === $options['trade_type'] && null === $openid) {
                    throw new MissingOptionsException('The required option "openid" is missing (when "trade_type" option is set to "JSAPI").');
                }

                return $openid;
            })
        ;

        $resolver
            ->define('receipt')
            ->default(null)
            ->allowedValues(null, 'Y')
        ;

        $resolver
            ->define('profit_sharing')
            ->default(null)
            ->allowedValues(null, 'Y', 'N')
        ;

        $resolver
            ->define('scene_info')
            ->default(null)
            ->allowedTypes('null', 'string')
        ;
    }

    protected function configureRequest(RequestOptions $request, array $options): void
    {
        $body = array_filter([
            'appid' => $options['appid'],
            'mch_id' => $options['mchid'],
            'device_info' => $options['device_info'],
            'nonce_str' => $options['noncestr'],
            'sign_type' => $options['sign_type'],
            'body' => $options['body'],
            'detail' => $options['detail'],
            'attach' => $options['attach'],
            'out_trade_no' => $options['out_trade_no'],
            'fee_type' => $options['fee_type'],
            'total_fee' => $options['total_fee'],
            'spbill_create_ip' => $options['client_ip'],
            'time_start' => $options['time_start'],
            'time_expire' => $options['time_expire'],
            'goods_tag' => $options['goods_tag'],
            'notify_url' => $options['notify_url'],
            'trade_type' => $options['trade_type'],
            'product_id' => $options['product_id'],
            'limit_pay' => $options['limit_pay'],
            'openid' => $options['openid'],
            'receipt' => $options['receipt'],
            'profit_sharing' => $options['profit_sharing'],
            'scene_info' => $options['scene_info'],
        ], fn ($value) => null !== $value);

        // Generate signature
        $body['sign'] = $this->signatureUtils->generate([
            'mchkey' => $options['mchkey'],
            'sign_type' => $options['sign_type'],
            'data' => $body,
        ]);

        $request
            ->setMethod('POST')
            ->setUrl($options['using_slave_url'] ? static::URL2 : static::URL)
            ->setBody($this->serializer->serialize($body, 'xml'))
        ;
    }
}
