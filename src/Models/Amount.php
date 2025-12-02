<?php

namespace ICEPAY\Checkout\Models;

class Amount
{
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_USD = 'USD';
    const CURRENCY_GBP = 'GBP';
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