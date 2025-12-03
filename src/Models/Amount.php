<?php

namespace ICEPAY\Checkout\Models;

class Amount implements \JsonSerializable
{
    const CURRENCY_EUR = 'eur';
    const CURRENCY_GBP = 'gbp';
    const CURRENCY_USD = 'usd';
    const CURRENCY_PLN = 'pln';
    const CURRENCY_SEK = 'sek';
    const CURRENCY_NOK = 'nok';
    const CURRENCY_DKK = 'dkk';
    const CURRENCY_CZK = 'czk';
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

    public function jsonSerialize(): mixed
    {
        $data = [
            'value' => $this->value,
        ];

        if ($this->currency !== null) {
            $data['currency'] = strtolower($this->currency);
        }

        return $data;
    }
}