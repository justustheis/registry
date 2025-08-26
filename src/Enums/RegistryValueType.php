<?php

namespace JustusTheis\Registry\Enums;

enum RegistryValueType: string
{
    /*
    |--------------------------------------------------------------------------
    | Registry Value Type Enum
    |--------------------------------------------------------------------------
    |
    | Defines the supported data types for registry values, providing
    | type safety and centralized management of value type casting
    | throughout the registry system.
    |
    */

    case STRING = 'string';
    case INTEGER = 'integer';
    case FLOAT = 'float';
    case BOOLEAN = 'boolean';
    case ARRAY = 'array';
    case OBJECT = 'object';

    /**
     * Process a raw value according to this type.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function processValue(mixed $value): mixed
    {
        return match ($this) {
            self::INTEGER => (int) $value,
            self::FLOAT => (float) $value,
            self::BOOLEAN => (bool) $value,
            self::ARRAY, self::OBJECT => is_string($value) ? json_decode($value, true) : $value,
            self::STRING => (string) $value,
        };
    }

    /**
     * Get all enum values as an array of strings.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}