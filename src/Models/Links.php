<?php

namespace ICEPAY\Checkout\Models;

use ICEPAY\Checkout\Models\JsonDeserializable;

class Links extends JsonDeserializable
{
    public string $direct;
    public string $checkout;
    public string $documentation;
}
