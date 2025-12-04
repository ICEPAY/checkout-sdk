<?php

namespace ICEPAY\Checkout\Models\Response;

use ICEPAY\Checkout\Models\Amount;
use ICEPAY\Checkout\Models\Payment;
use ICEPAY\Checkout\Models\PaymentMethod;
use ICEPAY\Checkout\Models\Recipient;
use ICEPAY\Checkout\Models\Status;

class Forward extends \ICEPAY\Checkout\Models\Forward
{
    public Payment $payment;
}