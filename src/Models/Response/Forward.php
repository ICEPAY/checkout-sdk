<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Models\Response;

use ICEPAY\Checkout\Models\Payment;

class Forward extends \ICEPAY\Checkout\Models\Forward
{
    public Payment $payment;
}
