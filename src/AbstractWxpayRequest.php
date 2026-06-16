<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\AbstractRequest;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @extends AbstractRequest<array>
 */
abstract class AbstractWxpayRequest extends AbstractRequest
{
    protected readonly SerializerInterface $serializer;
    protected readonly SignatureUtils $signatureUtils;

    public function __construct(?HttpClientInterface $httpClient = null, ?SerializerInterface $serializer = null, ?SignatureUtils $signatureUtils = null)
    {
        $this->serializer = $serializer ?? new Serializer([new ArrayDenormalizer()], [new XmlEncoder()]);
        $this->signatureUtils = $signatureUtils ?? new SignatureUtils();

        parent::__construct($httpClient);
    }

    protected function parseResponse(ResponseInterface $response): array
    {
        /**
         * @var array{
         *  return_code?: string,
         *  result_code?: string,
         *  return_msg?: string,
         *  err_code_des?: string
         * }
         */
        $data = $this->serializer->deserialize($response->getContent(), 'string[]', 'xml');

        $returnCode = $data['return_code'] ?? '';
        $resultCode = $data['result_code'] ?? '';

        if ('FAIL' === $returnCode) {
            throw new ParseResponseException($response, $data['return_msg'] ?? '', responseData: $data);
        }

        if ('FAIL' === $resultCode) {
            throw new ParseResponseException($response, $data['err_code_des'] ?? '', responseData: $data);
        }

        return $data;
    }
}
