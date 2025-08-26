<?php

namespace JustusTheis\Registry\Services;

use JustusTheis\Registry\Exceptions\RegistryValidationException;

class RegistryValueValidator
{
    /*
    |--------------------------------------------------------------------------
    | Registry Value Validator Service
    |--------------------------------------------------------------------------
    |
    | Validates and serializes registry values for storage ensuring they are
    | properly formatted and serializable before being stored in
    | the registry database.
    |
    */

    /**
     * Validates and serializes registry values for storage.
     *
     * @param  mixed                       $value
     * @throws RegistryValidationException
     * @return string|null
     */
    public static function handle(mixed $value) :string|null
    {
        if ($value === null) {
            return null;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        if (is_array($value) || is_object($value)) {
            $encoded = json_encode(($value));
            if (! $encoded) {
                throw RegistryValidationException::invalidValue('Value must be serializable.');
            }

            return $encoded;
        }

        if (! is_scalar($value)) {
            throw RegistryValidationException::invalidValue(
                'Value of type '.gettype($value).' is not serializable to string.'
            );
        }

        return (string) $value;
    }
}
