<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\ResolverInterface;
use Siganushka\ApiFactory\ResolverTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_4.shtml
 */
class ParameterUtils implements ResolverInterface
{
    use ResolverTrait;

    private SignatureUtils $signatureUtils;

    public function __construct(?SignatureUtils $signatureUtils = null)
    {
        $this->signatureUtils = $signatureUtils ?? new SignatureUtils();
    }

    /**
     * 生成 JSAPI 支付参数.
     *
     * @param array $options JSAPI 支付参数
     *
     * @return array JSAPI 支付参数
     */
    public function jsapi(array $options = []): array
    {
        $resolved = $this->resolve($options);
        $parameter = [
            'appId' => $resolved['appid'],
            'timeStamp' => $resolved['timestamp'],
            'nonceStr' => $resolved['noncestr'],
            'package' => \sprintf('prepay_id=%s', $options['prepay_id']),
            'signType' => $resolved['sign_type'],
        ];

        // Generate signature
        $parameter['paySign'] = $this->signatureUtils->generate([
            'mchkey' => $resolved['mchkey'],
            'sign_type' => $resolved['sign_type'],
            'data' => $parameter,
        ]);

        return $parameter;
    }

    /**
     * 生成 APP 支付参数.
     *
     * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_2_4.shtml
     *
     * @param array $options APP 支付参数选项
     */
    public function app(array $options = []): array
    {
        $resolved = $this->resolve($options);
        $parameter = [
            'appid' => $resolved['appid'],
            'partnerid' => $resolved['mchid'],
            'prepayid' => $options['prepay_id'],
            'package' => 'Sign=WXPay',
            'noncestr' => $resolved['noncestr'],
            'timestamp' => $resolved['timestamp'],
        ];

        // Generate signature
        $parameter['sign'] = $this->signatureUtils->generate([
            'mchkey' => $resolved['mchkey'],
            'sign_type' => $resolved['sign_type'],
            'data' => $parameter,
        ]);

        return $parameter;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::appid($resolver);
        OptionSet::mchid($resolver);
        OptionSet::mchkey($resolver);
        OptionSet::sign_type($resolver);
        OptionSet::timestamp($resolver);
        OptionSet::noncestr($resolver);

        $resolver
            ->define('prepay_id')
            ->required()
            ->allowedTypes('string')
        ;
    }
}
