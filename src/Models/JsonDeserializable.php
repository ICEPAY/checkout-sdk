<?php

namespace ICEPAY\Checkout\Models;

abstract class JsonDeserializable
{
    /** @param array<string, mixed>|string $data */
    public static function fromResponse(array|string $data): static
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return static::fromArray($data);
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
            $result->$key = $value;
        }
        return $result;
    }
}
