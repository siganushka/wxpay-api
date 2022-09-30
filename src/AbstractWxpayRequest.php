<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\AbstractRequest;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractWxpayRequest extends AbstractRequest
{
    protected SerializerInterface $serializer;
    protected SignatureUtils $signatureUtils;

    public function __construct(HttpClientInterface $httpClient = null, SerializerInterface $serializer = null, SignatureUtils $signatureUtils = null)
    {
        $this->serializer = $serializer ?? new Serializer([new ArrayDenormalizer()], [new XmlEncoder()]);
        $this->signatureUtils = $signatureUtils ?? new SignatureUtils();

        parent::__construct($httpClient);
    }
}
