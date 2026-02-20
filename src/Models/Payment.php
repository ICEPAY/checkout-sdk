<?php

namespace ICEPAY\Checkout\Models;

use DateTime;

class Payment extends JsonDeserializable
{
    public string $key;
    public Status $status;
    public FinancialStatus $financialStatus;
    public Amount $amount;
    public ?PaymentMethod $paymentMethod;
    public string $reference;
    public string $description;
    public ?Metadata $meta;
    public ?string $webhookUrl;
    public ?string $redirectUrl;
    public Merchant $merchant;
    public bool $isTest;
    public ?Datetime $expireAfter;
    public ?Datetime $createdAt;
    public ?Datetime $updatedAt;
    public Links $links;
}
