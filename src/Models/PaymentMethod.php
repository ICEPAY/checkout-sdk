<?php

namespace ICEPAY\Checkout\Models;

class PaymentMethod
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
    public static function fromArray(array $data): self
    {
        return new self($data['type'], $data['issuer'] ?? null);
    }
    public function toArray(): array
    {
        $result = [
            'type' => $this->type,
        ];

        if ($this->issuer !== null) {
            $result['issuer'] = $this->issuer;
        }
        return $result;
    }
}
