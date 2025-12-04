<?php

namespace ICEPAY\Checkout\Models;

class Recipient implements \JsonSerializable
{
    public function __construct(public string $id)
    {
    }
    public function withId(string $id) : self
    {
        $this->id = $id;
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
        ];
    }
}