<?php

namespace ICEPAY\Checkout\Models;

class Payment extends JsonDeserializable
{
    public string $key;
    public Status $status;
    public Amount $amount;
    public ?PaymentMethod $paymentMethod;
    public string $reference;
    public string $description;
    public Metadata $meta;
    public string $webhookUrl;
    public string $redirectUrl;
    public Merchant $merchant;
    public bool $isTest;
    public ?int $expireAfter;
    public ?int $createdAt;
    public ?int $updatedAt;
    public Links $links;
}
