<?php

namespace ICEPAY\Checkout\Models\Response;

use ICEPAY\Checkout\Models\Amount;
use ICEPAY\Checkout\Models\PaymentMethod;
use ICEPAY\Checkout\Models\Status;

class Refund
{
    public string $key;
    public Status $status;
    public Amount $amount;
    public string $reference;
    public string $description;
    public Checkout $payment;


    public static function fromResponse(array|string $data): self
    {
        if(is_string($data)) {
            $data = json_decode($data, true);
        }

        return self::fromArray($data);
    }

    public static function fromArray(array $data): self{
        $result = new self();

        $result->key = $data['key'];
        $result->status = Status::fromString($data['status']);
        $result->amount = new Amount($data['amount']['value'], $data['amount']['currency'] ?? null);
        $result->reference = $data['reference'];
        $result->description = $data['description'];
        $result->payment = Checkout::fromArray($data['payment']);

        return $result;
    }
}