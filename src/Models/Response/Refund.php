<?php

namespace ICEPAY\Checkout\Models\Response;


use ICEPAY\Checkout\Models\Payment;

class Refund extends \ICEPAY\Checkout\Models\Refund
{
    public Payment $payment;
}