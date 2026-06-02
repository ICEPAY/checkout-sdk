<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Models;

/** @phpstan-consistent-constructor */
class PaymentMethod extends JsonDeserializable implements \JsonSerializable
{
    public const TYPE_BANCONTACT = 'bancontact';
    public const TYPE_IDEAL = 'ideal';
    public const TYPE_ONLINEUEBERWEISEN = 'onlineueberweisen';
    public const TYPE_CARD = 'card';
    public const TYPE_PAYPAL = 'paypal';
    public const TYPE_EPS = 'EPS';
    public const TYPE_PAYBYBANK = 'paybybank';

    public function __construct(public string $type, public ?string $issuer = null)
    {
    }
    public function withType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
    public function withIssuer(string $issuer): self
    {
        $this->issuer = $issuer;
        return $this;
    }
    public function jsonSerialize(): mixed
    {
        $result = [
            'type' => $this->type,
        ];

        if ($this->issuer !== null) {
            $result['issuer'] = $this->issuer;
        }
        return $result;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new static(
            type: $data['type'],
            issuer: $data['issuer'] ?? null
        );
    }
}
