<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Models\Request;

use ICEPAY\Checkout\Models\Amount;
use ICEPAY\Checkout\Models\Metadata;
use ICEPAY\Checkout\Models\PaymentMethod;

class Checkout implements \JsonSerializable
{
    public function __construct(
        public string $reference = '',
        public string $description = '',
        public ?Amount $amount = null,
        public string $redirectUrl = '',
        public string $webhookUrl = '',
        public PaymentMethod|string $paymentMethod = '',
        public Metadata $metadata = new Metadata(),
        public ?int $expireAfter = null
    ) {
    }
    public function withReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }
    public function withDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
    public function withRedirectUrl(string $redirectUrl): self
    {
        $this->redirectUrl = $redirectUrl;
        return $this;
    }
    public function withWebhookUrl(string $webhookUrl): self
    {
        $this->webhookUrl = $webhookUrl;
        return $this;
    }
    /** @param array<string, mixed> $customer */
    public function withCustomer(array $customer): self
    {
        $this->metadata->withCustomer($customer);
        return $this;
    }
    public function withCustomerEmail(string $email): self
    {
        $this->metadata->withCustomerEmail($email);
        return $this;
    }
    public function withIntegrationInformation(string $type, string $version, string $developer): self
    {
        $this->metadata->withIntegrationInformation($type, $version, $developer);
        return $this;
    }
    public function jsonSerialize(): mixed
    {
        $data = [
            'reference' => $this->reference,
            'description' => $this->description,
            'amount' => $this->amount?->jsonSerialize(),
            'redirectUrl' => $this->redirectUrl,
            'webhookUrl' => $this->webhookUrl,
            'metadata' => $this->metadata->jsonSerialize(),
        ];

        if ($this->paymentMethod instanceof PaymentMethod) {
            $data['paymentMethod'] = $this->paymentMethod->jsonSerialize();
        } elseif ($this->paymentMethod !== '') {
            $data['paymentMethod'] = ['type' => $this->paymentMethod];
        }

        if ($this->expireAfter !== null) {
            $data['expireAfter'] = $this->expireAfter;
        }

        return $data;
    }
}
