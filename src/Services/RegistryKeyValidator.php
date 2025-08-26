<?php

namespace JustusTheis\Registry\Services;

use JustusTheis\Registry\Exceptions\RegistryValidationException;

class RegistryKeyValidator
{
    /*
    |--------------------------------------------------------------------------
    | Registry Key Validator Service
    |--------------------------------------------------------------------------
    |
    | Validates and normalizes registry key formats ensuring they contain only
    | allowed characters while converting path separators to dot
    | notation for consistent storage.
    |
    */

    /**
     * Validate and normalize a registry key format.
     *
     * Converts backslashes and forward slashes to dots and validates that the key
     * contains only allowed characters (alphanumeric, dots, underscores, hyphens).
     *
     * @param  string                      $key The registry key to validate
     * @throws RegistryValidationException If key contains invalid characters
     * @return string                      The normalized and validated key
     */
    public static function handle(string $key) :string
    {
        $key = str_replace(['\\', '/'], '.', $key);

        if (! preg_match('/^[a-zA-Z0-9._-]+$/', $key)) {
            throw RegistryValidationException::invalidKeyFormat($key);
        }

        return $key;
    }
}
