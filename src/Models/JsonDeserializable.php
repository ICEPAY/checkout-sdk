<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Models;

use ICEPAY\Checkout\Json;

abstract class JsonDeserializable
{
    /**
     * @param array<string, mixed>|string $data
     * @throws \JsonException When a JSON string is malformed.
     */
    public static function fromResponse(array|string $data): static
    {
        return static::fromArray(is_string($data) ? Json::decode($data) : $data);
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        $result = (new \ReflectionClass(static::class))->newInstanceWithoutConstructor();
        foreach ($data as $key => $value) {
            if (!property_exists($result, $key)) {
                continue;
            }
            $propertyType = (new \ReflectionProperty($result, $key))->getType();

            if ($value === null) {
                if ($propertyType === null || $propertyType->allowsNull()) {
                    $result->$key = null;
                }
                continue;
            }

            if (is_array($value)) {
                if ($propertyType instanceof \ReflectionNamedType && !$propertyType->isBuiltin()) {
                    $propertyClass = $propertyType->getName();
                    if (is_subclass_of($propertyClass, JsonDeserializable::class)) {
                        $result->$key = $propertyClass::fromArray($value);
                        continue;
                    }
                }
            }
            if ($propertyType instanceof \ReflectionNamedType && $propertyType->getName() === \DateTime::class) {
                $result->$key = new \DateTime($value);
                continue;
            }
            if ($propertyType instanceof \ReflectionNamedType && enum_exists($propertyType->getName())) {
                $enumClass = $propertyType->getName();
                if (method_exists($enumClass, 'fromString')) {
                    $result->$key = $enumClass::fromString($value);
                    continue;
                }
            }
            if ($propertyType instanceof \ReflectionNamedType && $propertyType->isBuiltin()) {
                settype($value, $propertyType->getName());
            }
            $result->$key = $value;
        }
        return $result;
    }
}
