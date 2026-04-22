<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Models\Response;

use ICEPAY\Checkout\Models\Payment;
use ICEPAY\Checkout\Models\PaymentMethod;

class Checkout extends Payment
{
    /** @var list<\ICEPAY\Checkout\Models\Refund>|null */
    public ?array $refunds;
    /** @var list<\ICEPAY\Checkout\Models\Forward>|null */
    public ?array $forwards;

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        $result = parent::fromArray($data);

        if (isset($data['paymentMethod'])) {
            $result->paymentMethod = PaymentMethod::fromArray($data['paymentMethod']);
        }
        if (isset($data['refunds'])) {
            $result->refunds = [];
            foreach ($data['refunds'] as $refundData) {
                $result->refunds[] = \ICEPAY\Checkout\Models\Refund::fromArray($refundData);
            }
        }
        if (isset($data['forwards'])) {
            $result->forwards = [];
            foreach ($data['forwards'] as $forwardData) {
                $result->forwards[] = \ICEPAY\Checkout\Models\Forward::fromArray($forwardData);
            }
        }

        return $result;
    }
}
