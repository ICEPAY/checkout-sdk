<?php

namespace ICEPAY\Checkout\Models;

enum Status
{
    case started;
    case completed;
    case pending;
    case expired;
    case cancelled;

    public function toString(): string
    {
        return match ($this) {
            Status::started => 'started',
            Status::completed => 'completed',
            Status::pending => 'pending',
            Status::expired => 'expired',
            Status::cancelled => 'cancelled',
        };
    }

    public static function fromString(string $status): Status
    {
        return match ($status) {
            'started' => Status::started,
            'completed' => Status::completed,
            'pending' => Status::pending,
            'expired' => Status::expired,
            'cancelled' => Status::cancelled,
            default => throw new \InvalidArgumentException("Invalid status: $status"),
        };
    }
}
