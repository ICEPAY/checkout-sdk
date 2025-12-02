<?php

namespace ICEPAY\Checkout\Models\Request;

class Refund
{
    public function __construct(public $reference)
    {
    }
}