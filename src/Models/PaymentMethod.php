<?php

namespace ICEPAY\Checkout\Models;

/** @phpstan-consistent-constructor */
class PaymentMethod extends JsonDeserializable implements \JsonSerializable
{
    const TYPE_BANCONTACT = 'bancontact';
    const TYPE_IDEAL = 'ideal';
    const TYPE_ONLINEUEBERWEISEN = 'onlineueberweisen';
    const TYPE_CARD = 'card';
    const TYPE_PAYPAL = 'paypal';
    const TYPE_EPS = 'EPS';
    const TYPE_PAYBYBANK = 'paybybank';

    public function __construct(public string $type, public ?string $issuer = null)
    {
    }
    public function withType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
    public function withIssuer(string $issuer): self
    {
        $this->issuer = $issuer;
        return $this;
    }
    public function jsonSerialize(): mixed
    {
        $result = [
            'type' => $this->type,
        ];

        if ($this->issuer !== null) {
            $result['issuer'] = $this->issuer;
        }
        return $result;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new static(
            type: $data['type'],
            issuer: $data['issuer'] ?? null
        );
    }
}
