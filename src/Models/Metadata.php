<?php

namespace ICEPAY\Checkout\Models;

/** @phpstan-consistent-constructor */
class Metadata extends JsonDeserializable implements \JsonSerializable
{
    /** @param array<string, mixed> $data */
    public function __construct(protected array $data = [])
    {
    }

    /** @param array<string, mixed> $customer */
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
    public function withIntegrationInformation(string $type, string $version, string $developer): self
    {
        $this->data['integration'] = array_merge($this->data['integration'] ?? [], [
            'type' => $type,
            'version' => $version,
            'developer' => $developer,
        ]);
        return $this;
    }
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }
    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
