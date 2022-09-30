<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\RequestOptions;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
 */
class Transfer extends AbstractWxpayRequest
{
    public const URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::appid($resolver);
        OptionsUtils::mchid($resolver);
        OptionsUtils::mchkey($resolver);
        OptionsUtils::mch_client_cert($resolver);
        OptionsUtils::mch_client_key($resolver);
        OptionsUtils::sign_type($resolver);
        OptionsUtils::noncestr($resolver);
        OptionsUtils::client_ip($resolver);

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
        $body['sign'] = $this->signatureUtils->generate([
            'mchkey' => $options['mchkey'],
            'sign_type' => $options['sign_type'],
            'data' => $body,
        ]);

        $request
            ->setMethod('POST')
            ->setUrl(static::URL)
            ->setBody($this->serializer->serialize($body, 'xml'))
            ->setLocalCert($options['mch_client_cert'])
            ->setLocalPk($options['mch_client_key'])
        ;
    }

    protected function parseResponse(ResponseInterface $response): array
    {
        $result = $this->serializer->deserialize($response->getContent(), 'string[]', 'xml');

        $returnCode = (string) ($result['return_code'] ?? '');
        $resultCode = (string) ($result['result_code'] ?? '');

        if ('FAIL' === $returnCode) {
            throw new ParseResponseException($response, (string) ($result['return_msg'] ?? ''));
        }

        if ('FAIL' === $resultCode) {
            throw new ParseResponseException($response, (string) ($result['err_code_des'] ?? ''));
        }

        return $result;
    }
}
