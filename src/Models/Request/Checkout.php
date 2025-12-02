<?php

namespace ICEPAY\Checkout\Models\Request;

use ICEPAY\Checkout\Models\Amount;
use ICEPAY\Checkout\Models\Metadata;

class Checkout
{
    public function __construct(public string $reference = '',
                                public string $description = '',
                                public ?Amount $amount,
                                public string $redirectUrl = '',
                                public string $webhookUrl = '',
                                public Metadata $metadata)
    {
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

    public function withWebhookUrl(string $webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;
        return $this;
    }

    public function withCustomer(array $customer): self
    {
        $this->metadata->withCustomer($customer);
        return $this;
    }

    public function withCustomerEmail(string $email): self
    {
        if (!isset($this->metadata)) {
            $this->metadata = new Metadata();
        }

        $this->metadata->withCustomerEmail($email);
        return $this;
    }

    public function toArray(): array
    {
        $data = [
            'reference'   => $this->reference,
            'description' => $this->description,
            'amount'      => $this->amount?->toArray(),
            'redirectUrl' => $this->redirectUrl,
            'webhookUrl'  => $this->webhookUrl,
            'metadata'    => $this->metadata?->toArray(),
        ];

        return $data;
    }
}