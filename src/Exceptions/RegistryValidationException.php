<?php

namespace JustusTheis\Registry\Exceptions;

class RegistryValidationException extends RegistryException
{
    /*
    |--------------------------------------------------------------------------
    | Registry Validation Exception
    |--------------------------------------------------------------------------
    |
    | This exception is thrown when registry operations fail validation rules.
    | It provides specific factory methods for common validation scenarios
    | such as invalid key formats, empty keys, and model validation errors.
    |
    */

    /**
     * Create a validation exception for invalid registry key format.
     *
     * Registry keys must follow specific formatting rules including only
     * alphanumeric characters, dots, underscores, and hyphens.
     *
     * @param  string $key The invalid registry key
     * @return self   The validation exception instance
     */
    public static function invalidKeyFormat(string $key): self
    {
        $exception = new self("Invalid key format: '{$key}'. Keys must contain only alphanumeric characters, dots, underscores, and hyphens.");

        return $exception->setRegistryKey($key);
    }

    /**
     * Create a validation exception for empty registry keys.
     *
     * Registry keys cannot be empty strings as they serve as unique
     * identifiers for stored values.
     *
     * @return self The validation exception instance
     */
    public static function emptyKey(): self
    {
        return new self('Registry key cannot be empty.');
    }

    /**
     * Create a validation exception for invalid registry values.
     *
     * This method creates an exception when a registry value fails
     * validation rules with a specific reason for the failure.
     *
     * @param  string $reason The reason why the value is invalid
     * @return self   The validation exception instance
     */
    public static function invalidValue(string $reason): self
    {
        $exception = new self("Invalid value: {$reason}");

        return $exception->setRegistryKey(null);
    }

    /**
     * Create a validation exception for invalid model usage.
     *
     * Models must implement the HasRegistry trait to be used with
     * the registry scoping functionality.
     *
     * @param  string $modelClass The class name of the invalid model
     * @return self   The validation exception instance
     */
    public static function invalidModel(string $modelClass): self
    {
        return new self("Model '{$modelClass}' must use the HasRegistry trait to be used with the registry.");
    }
}
