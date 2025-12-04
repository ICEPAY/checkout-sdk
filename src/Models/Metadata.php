<?php

namespace ICEPAY\Checkout\Models;

class Metadata
{
    public function __construct(protected array $data = [])
    {
    }

    public function withCustomer(array $customer): self
    {
        $this->data['customer'] = $customer;
        return $this;
    }

    public function withCustomerEmail(string $email): self
    {
        if (!isset($this->data['customer'])) {
            $this->data['customer'] = [];
        }

        $this->data['customer']['email'] = $email;
        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
