<?php

namespace ICEPAY\Checkout\Models\Forward;

class Recipient
{
    public function __construct(public string $id)
    {
    }
    public function withId(string $id) : self
    {
        $this->id = $id;
        return $this;
    }
    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}