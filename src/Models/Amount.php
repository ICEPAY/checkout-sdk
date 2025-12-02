<?php

namespace ICEPAY\Checkout\Models;

class Amount
{
    public function __construct(public int $value, public ?string $currency)
    {
    }

    public function withValue(int $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function withCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function toArray()
    {
        $data = [
            'value' => $this->value,
        ];

        if ($this->currency !== null) {
            $data['currency'] = $this->currency;
        }

        return $data;
    }
}