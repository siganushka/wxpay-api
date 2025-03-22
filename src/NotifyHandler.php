<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay;

use Siganushka\ApiFactory\ResolverInterface;
use Siganushka\ApiFactory\ResolverTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class NotifyHandler implements ResolverInterface
{
    use ResolverTrait;

    protected SerializerInterface $serializer;
    protected SignatureUtils $signatureUtils;

    public function __construct(?SerializerInterface $serializer = null, ?SignatureUtils $signatureUtils = null)
    {
        $this->serializer = $serializer ?? new Serializer([new ArrayDenormalizer()], [new XmlEncoder()]);
        $this->signatureUtils = $signatureUtils ?? new SignatureUtils();
    }

    /**
     * @param array $data    微信支付结果通知数据
     * @param array $options 自定义选项
     *
     * @return array 微信支付结果通知数据
     */
    public function handle(array $data, array $options = []): array
    {
        $resolved = $this->resolve($options);

        $signature = $data['sign'] ?? '';
        unset($data['sign']);

        if (!$this->signatureUtils->verify($signature, $data, $resolved)) {
            throw new \RuntimeException('Invalid signature.');
        }

        return $data;
    }

    public function handleRequest(Request $request): array
    {
        /** @var array<string, string> */
        $data = $this->serializer->deserialize($request->getContent(), 'string[]', 'xml');

        return $this->handle($data);
    }

    public function success(?string $message = null): Response
    {
        return $this->createXmlResponse('SUCCESS', $message)->send();
    }

    public function fail(?string $message = null): Response
    {
        return $this->createXmlResponse('FAIL', $message)->send();
    }

    protected function createXmlResponse(string $code, ?string $message): Response
    {
        $data = array_filter([
            'return_code' => $code,
            'return_msg' => $message,
        ], fn ($value) => \is_string($value));

        $content = $this->serializer->serialize($data, 'xml', [
            'xml_root_node_name' => 'xml',
            'xml_encoding' => 'UTF-8',
            'xml_version' => '1.0',
        ]);

        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::mchkey($resolver);
        OptionSet::sign_type($resolver);
    }
}
