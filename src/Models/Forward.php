<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Models;

class Forward extends JsonDeserializable implements \JsonSerializable
{
    public string $key;
    public Status $status;
    public Amount $amount;
    public string $reference;
    public string $description;
    public Recipient $recipient;

    public function jsonSerialize(): mixed
    {
        return [
            'key' => $this->key,
            'status' => $this->status->toString(),
            'amount' => $this->amount->jsonSerialize(),
            'reference' => $this->reference,
            'description' => $this->description,
            'recipient' => $this->recipient,
        ];
    }
}
