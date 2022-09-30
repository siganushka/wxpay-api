<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\ResolverInterface;
use Siganushka\ApiFactory\ResolverTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=4_3
 */
class SignatureUtils implements ResolverInterface
{
    use ResolverTrait;

    /**
     * 生成数据签名.
     *
     * @param array $options 数据签名选项
     *
     * @return string 数据签名
     */
    public function generate(array $options = []): string
    {
        $resolved = $this->resolve($options);
        $rawData = $resolved['data'];

        ksort($rawData);
        $rawData['key'] = $resolved['mchkey'];

        $signature = http_build_query($rawData);
        $signature = urldecode($signature);

        $signature = (OptionsUtils::SIGN_TYPE_SHA256 === $resolved['sign_type'])
            ? hash_hmac('sha256', $signature, $resolved['mchkey'])
            : hash('md5', $signature);

        return strtoupper($signature);
    }

    public function verify(string $signature, array $options = []): bool
    {
        return 0 === strcmp($signature, $this->generate($options));
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::mchkey($resolver);
        OptionsUtils::sign_type($resolver);

        $resolver
            ->define('data')
            ->required()
            ->allowedTypes('array')
        ;
    }
}
