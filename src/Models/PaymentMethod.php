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
    public function __construct(public string $type){
    }
    public static function fromArray(array $data): self
    {
        return new self($data['type']);
    }
    public function toArray(): array
    {
        return [
            'type' => $this->type,
        ];
    }
}
