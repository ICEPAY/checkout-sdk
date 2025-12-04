<?php

namespace ICEPAY\Checkout\Models\Response;

use ICEPAY\Checkout\Models\Payment;

class Forward extends \ICEPAY\Checkout\Models\Forward
{
    public Payment $payment;
}
