<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Models;

class Merchant extends JsonDeserializable
{
    public string $id;
    public string $name;
}
