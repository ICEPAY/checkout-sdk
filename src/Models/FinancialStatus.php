<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Models;

use JsonSerializable;

enum FinancialStatus implements JsonSerializable
{
    case uncleared;
    case cleared;

    public function toString(): string
    {
        return match ($this) {
            FinancialStatus::uncleared => 'uncleared',
            FinancialStatus::cleared => 'cleared',
        };
    }

    public static function fromString(string $status): FinancialStatus
    {
        return match ($status) {
            'uncleared' => FinancialStatus::uncleared,
            'cleared' => FinancialStatus::cleared,
            default => throw new \InvalidArgumentException("Invalid financial status: $status"),
        };
    }

    public function jsonSerialize(): mixed
    {
        return $this->toString();
    }
}
