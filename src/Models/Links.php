<?php

namespace ICEPAY\Checkout\Models;

class Links extends JsonDeserializable
{
    public ?string $direct;
    public string $checkout;
    public string $documentation;
}
