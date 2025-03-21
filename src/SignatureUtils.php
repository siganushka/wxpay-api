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
     * @param array $data    待签名数据
     * @param array $options 自定义选项
     *
     * @return string 数据签名
     */
    public function generate(array $data, array $options = []): string
    {
        $resolved = $this->resolve($options);

        ksort($data);
        $data['key'] = $resolved['mchkey'];

        $stringToSignature = http_build_query($data);
        $stringToSignature = urldecode($stringToSignature);

        $signature = (OptionSet::SIGN_TYPE_SHA256 === $resolved['sign_type'])
            ? hash_hmac('sha256', $stringToSignature, $resolved['mchkey'])
            : hash('md5', $stringToSignature);

        return strtoupper($signature);
    }

    public function verify(string $signature, array $data, array $options = []): bool
    {
        return 0 === strcmp($signature, $this->generate($data, $options));
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::mchkey($resolver);
        OptionSet::sign_type($resolver);
    }
}
