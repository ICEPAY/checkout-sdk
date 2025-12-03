<?php

namespace ICEPAY\Checkout\Models\Response;

use ICEPAY\Checkout\Models\Payment;
use ICEPAY\Checkout\Models\PaymentMethod;

class Checkout extends Payment
{
    public ?array $refunds;
    public ?array $forwards;
    public static function fromArray(array $data): static{
        $result = parent::fromArray($data);

        if(isset($data['paymentMethod'])){
            $result->paymentMethod = PaymentMethod::fromArray($data['paymentMethod']);
        }
        if(isset($data['refunds'])){
            $result->refunds = [];
            foreach($data['refunds'] as $refundData){
                $result->refunds[] = Refund::fromArray($refundData);
            }
        }
        if(isset($data['forwards'])){
            $result->forwards = [];
            foreach($data['forwards'] as $forwardData){
                $result->forwards[] = Forward::fromArray($forwardData);
            }
        }

        return $result;
    }
}
