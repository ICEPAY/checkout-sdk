<?php

namespace ICEPAY\Checkout\Models\Response;

use ICEPAY\Checkout\Models\JsonDeserializable;

class PaymentMethod extends JsonDeserializable
{
    public string $id;
    public string $logo;
    public string $description;
}
