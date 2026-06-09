<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wxpay\Exception;

class InvalidSignatureException extends \RuntimeException
{
    public function __construct(
        private readonly string $signature,
        private readonly array $data)
    {
        parent::__construct('Invalid signature.');
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
