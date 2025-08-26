<?php

namespace JustusTheis\Registry\Services;

class RegistryTypeCaster
{
    /*
    |--------------------------------------------------------------------------
    | Registry Type Caster Service
    |--------------------------------------------------------------------------
    |
    | Handles automatic type casting and conversion for registry values based on
    | configuration rules and explicit type specifications including JSON
    | parsing and boolean conversion logic.
    |
    */

    /**
     * Cast a value to a specified type or auto-detect and cast based on configuration.
     *
     * @param  mixed       $value The value to cast
     * @param  string|null $type  The target type (optional)
     * @return mixed       The casted value
     */
    public static function cast($value, ?string $type = null)
    {
        if ($type !== null) {
            return static::castToExplicitType($value, $type);
        }

        if (! config('registry.auto_cast_types', true)) {
            return $value;
        }

        return static::autoDetectAndCast($value);
    }

    /**
     * Cast a value to an explicitly specified type.
     *
     * @param  mixed  $value The value to cast
     * @param  string $type  The target type
     * @return mixed  The casted value
     */
    protected static function castToExplicitType($value, string $type)
    {
        return match ($type) {
            'string' => (string) $value,
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'bool', 'boolean' => static::castToBoolean($value),
            'array'  => static::castToArray($value),
            'object' => static::castToObject($value),
            'null'   => null,
            default  => $value,
        };
    }

    /**
     * Auto-detect the value type and cast accordingly based on configuration rules.
     *
     * @param  mixed $value The value to cast
     * @return mixed The casted value
     */
    protected static function autoDetectAndCast($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        $castRules = config('registry.cast_rules', []);

        if (static::isNullValue($value, $castRules)) {
            return;
        }

        if (static::isBooleanValue($value, $castRules)) {
            return static::getBooleanValue($value, $castRules);
        }

        if (config('registry.cast_rules.array_detection', true) && static::isJsonArray($value)) {
            return static::getJsonArray($value);
        }

        if (config('registry.cast_rules.object_detection', true) && static::isJsonObject($value)) {
            return static::getJsonObject($value);
        }

        if (config('registry.cast_rules.numeric_detection', true) && static::isNumericValue($value, $castRules)) {
            return static::getNumericValue($value, $castRules);
        }

        return $value;
    }

    /**
     * Check if the given value should be considered null based on cast rules.
     *
     * @param  string               $value     The value to check
     * @param  array<string, mixed> $castRules The casting rules configuration
     * @return bool                 True if value should be null
     */
    protected static function isNullValue(string $value, array $castRules): bool
    {
        $nullValues = $castRules['null_values'] ?? ['null'];

        return in_array(strtolower($value), array_map('strtolower', $nullValues), true);
    }

    /**
     * Check if the given value should be considered a boolean based on cast rules.
     *
     * @param  string               $value     The value to check
     * @param  array<string, mixed> $castRules The casting rules configuration
     * @return bool                 True if value should be boolean
     */
    protected static function isBooleanValue(string $value, array $castRules): bool
    {
        $trueValues = $castRules['boolean_true_values'] ?? ['true', 'yes', 'on'];
        $falseValues = $castRules['boolean_false_values'] ?? ['false', 'no', 'off'];
        $strictMode = $castRules['strict_boolean_mode'] ?? true;

        $allBooleanValues = array_merge($trueValues, $falseValues);

        if (! $strictMode) {
            $allBooleanValues = array_merge($allBooleanValues, ['0', '1']);
        }

        return in_array(strtolower($value), array_map('strtolower', $allBooleanValues), true);
    }

    /**
     * Convert a string value to boolean based on cast rules.
     *
     * @param  string               $value     The value to convert
     * @param  array<string, mixed> $castRules The casting rules configuration
     * @return bool                 The boolean value
     */
    protected static function getBooleanValue(string $value, array $castRules): bool
    {
        $trueValues = $castRules['boolean_true_values'] ?? ['true', 'yes', 'on'];
        $strictMode = $castRules['strict_boolean_mode'] ?? true;

        if (! $strictMode && in_array($value, ['0', '1'], true)) {
            return $value === '1';
        }

        return in_array(strtolower($value), array_map('strtolower', $trueValues), true);
    }

    /**
     * Check if the given value should be considered numeric based on cast rules.
     *
     * @param  string               $value     The value to check
     * @param  array<string, mixed> $castRules The casting rules configuration
     * @return bool                 True if value should be numeric
     */
    protected static function isNumericValue(string $value, array $castRules): bool
    {
        if (! is_numeric($value)) {
            return false;
        }

        $strictMode = $castRules['strict_boolean_mode'] ?? true;

        if ($strictMode && in_array($value, ['0', '1'], true)) {
            return true;
        }

        if (! $strictMode && in_array($value, ['0', '1'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Convert a string value to numeric (int or float) based on its format.
     *
     * @param  string               $value     The value to convert
     * @param  array<string, mixed> $castRules The casting rules configuration
     * @return int|float            The numeric value
     */
    protected static function getNumericValue(string $value, array $castRules)
    {
        if (str_contains($value, '.') || str_contains($value, 'e') || str_contains($value, 'E')) {
            return (float) $value;
        }

        return (int) $value;
    }

    /**
     * Cast any value to boolean using various conversion strategies.
     *
     * @param  mixed $value The value to cast
     * @return bool  The boolean value
     */
    protected static function castToBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        // For numeric strings (including '0', '1'), convert to number first
        if (is_numeric($value)) {
            return (bool) (float) $value;
        }

        // For other strings, check common boolean representations
        if (is_string($value)) {
            $lowerValue = strtolower(trim($value));

            if (in_array($lowerValue, ['true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($lowerValue, ['false', 'no', 'off'], true)) {
                return false;
            }
        }

        // Default PHP boolean conversion
        return (bool) $value;
    }

    /**
     * Cast any value to array, attempting JSON decoding for strings.
     *
     * @param  mixed                    $value The value to cast
     * @return array<string|int, mixed>
     */
    protected static function castToArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return (array) $value;
    }

    /**
     * Cast any value to object, attempting JSON decoding for strings.
     *
     * @param  mixed  $value The value to cast
     * @return object The object value
     */
    protected static function castToObject($value): object
    {
        if (is_object($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE && is_object($decoded)) {
                return $decoded;
            }
        }

        return (object) $value;
    }

    /**
     * Check if a string value represents a valid JSON array.
     *
     * @param  string $value The string to check
     * @return bool   True if string is a valid JSON array
     */
    protected static function isJsonArray(string $value): bool
    {
        $trimmed = trim($value);

        // Only detect JSON arrays (start with [ and end with ])
        // JSON objects (start with { and end with }) are not auto-detected as arrays
        if (! str_starts_with($trimmed, '[') || ! str_ends_with($trimmed, ']')) {
            return false;
        }

        // Validate JSON and ensure it decodes to an array
        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded);
    }

    /**
     * Decode a JSON string to array.
     *
     * @param  string                   $value The JSON string
     * @return array<string|int, mixed> The decoded array or empty array on failure
     */
    protected static function getJsonArray(string $value): array
    {
        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Check if a string value represents a valid JSON object.
     *
     * @param  string $value The string to check
     * @return bool   True if string is a valid JSON object
     */
    protected static function isJsonObject(string $value): bool
    {
        $trimmed = trim($value);

        // Only detect JSON objects (start with { and end with })
        if (! str_starts_with($trimmed, '{') || ! str_ends_with($trimmed, '}')) {
            return false;
        }

        // Validate JSON
        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return false;
        }

        // Empty object {} should be detected as object
        if (empty($decoded)) {
            return true;
        }

        // Non-empty array should be associative (not sequential) to be considered object
        return ! array_is_list($decoded);
    }

    /**
     * Decode a JSON string to object.
     *
     * @param  string $value The JSON string
     * @return object The decoded object or empty stdClass on failure
     */
    protected static function getJsonObject(string $value): object
    {
        $decoded = json_decode($value, false); // Decode as object, not array

        return is_object($decoded) ? $decoded : new \stdClass();
    }
}
