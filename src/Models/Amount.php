<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Models;

class Amount extends JsonDeserializable implements \JsonSerializable
{
    public const CURRENCY_EUR = 'eur';
    public const CURRENCY_GBP = 'gbp';
    public const CURRENCY_USD = 'usd';
    public const CURRENCY_PLN = 'pln';
    public const CURRENCY_SEK = 'sek';
    public const CURRENCY_NOK = 'nok';
    public const CURRENCY_DKK = 'dkk';
    public const CURRENCY_CZK = 'czk';

    public function __construct(public ?int $value = null, public ?string $currency = null)
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
