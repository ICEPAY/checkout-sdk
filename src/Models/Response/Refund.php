<?php

namespace ICEPAY\Checkout\Models\Response;


use ICEPAY\Checkout\Models\Payment;

class Refund extends \ICEPAY\Checkout\Models\Refund
{
    public Payment $payment;

    public static function fromResponse(array|string $data): static
    {
        if(is_string($data)) {
            $data = json_decode($data, true);
        }

        return parent::fromArray($data);
    }

    public static function fromArray(array $data): static{
        $result = parent::fromArray($data);

        return $result;
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}