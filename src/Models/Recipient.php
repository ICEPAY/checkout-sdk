<?php

namespace ICEPAY\Checkout\Models;

class Recipient
{
    public function __construct(public int $id)
    {
    }
    public function withId(int $id): self
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

    public static function fromArray(mixed $recipient)
    {
        return new self($recipient['id']);
    }
}