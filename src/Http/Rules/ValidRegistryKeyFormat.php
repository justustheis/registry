<?php

namespace JustusTheis\Registry\Http\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use JustusTheis\Registry\Services\RegistryKeyValidator;
use JustusTheis\Registry\Exceptions\RegistryValidationException;

class ValidRegistryKeyFormat implements ValidationRule
{
    /*
    |--------------------------------------------------------------------------
    | Valid Registry Key Format Validation Rule
    |--------------------------------------------------------------------------
    |
    | Validates registry key format using the centralized RegistryKeyValidator
    | service, ensuring consistent validation logic across HTTP requests
    | and internal Registry operations.
    |
    */

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            RegistryKeyValidator::handle($value);
        } catch (RegistryValidationException $e) {
            $fail('The :attribute must contain only letters, numbers, dots, underscores, and hyphens.');
        }
    }
}