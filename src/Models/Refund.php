<?php

namespace ICEPAY\Checkout\Models;

class Refund extends JsonDeserializable
{
    public string $id;
    public Status $status;
    public Amount $amount;
    public string $reference;
    public string $description;
}
