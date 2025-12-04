<?php

namespace ICEPAY\Checkout\Models;

class Metadata extends JsonDeserializable implements \JsonSerializable
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
    public static function fromArray(array $data): static
    {
        return new static($data);
    }
    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
