<?php

namespace ICEPAY\Checkout\Models\Request;

use ICEPAY\Checkout\Models\Amount;
use ICEPAY\Checkout\Models\Forward\Recipient;

class Forward
{
    public function __construct(public $reference, public Amount|int $amount, public Recipient|string $recipient, public ?string $description)
    {
    }

    public function withReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function withAmount(Amount|int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function withDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function withRecipient(Recipient|string $recipient): self
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function toArray(): array
    {
        $data = [
            'reference' => $this->reference,
        ];

        if ($this->amount instanceof Amount) {
            $data['amount'] = $this->amount->toArray();
        } else {
            $data['amount'] = ['value' => $this->amount];
        }

        if ($this->recipient instanceof Recipient) {
            $data['recipient'] = $this->recipient->toArray();
        } else {
            $data['recipient'] = ['id' => $this->recipient];
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        return $data;
    }
}