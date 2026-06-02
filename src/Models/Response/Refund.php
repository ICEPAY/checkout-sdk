<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Models\Response;

use ICEPAY\Checkout\Models\Payment;

class Refund extends \ICEPAY\Checkout\Models\Refund
{
    public Payment $payment;
}
