<?php

declare(strict_types=1);

namespace ICEPAY\Checkout;

/**
 * @internal Shared JSON decoding so every entry point handles malformed and empty input the same way.
 */
final class Json
{
    /**
     * @return array<int|string, mixed>
     * @throws \JsonException When the input is not valid JSON.
     */
    public static function decode(string $json): array
    {
        if ($json === '') {
            return [];
        }

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR) ?? [];
    }
}
