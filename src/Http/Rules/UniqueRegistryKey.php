<?php

namespace JustusTheis\Registry\Http\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use JustusTheis\Registry\Facades\Registry;

class UniqueRegistryKey implements ValidationRule
{
    /*
    |--------------------------------------------------------------------------
    | Unique Registry Key Validation Rule
    |--------------------------------------------------------------------------
    |
    | Validates that a registry key does not already exist in the system,
    | ensuring uniqueness across all registry entries to prevent
    | duplicate key conflicts during creation and updates.
    |
    */

    /**
     * Create a new unique registry key validation rule.
     *
     * @param string|null $excludeKey Optional key to exclude from uniqueness check
     */
    public function __construct(private ?string $excludeKey = null)
    {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Allow the excluded key to pass validation (useful for updates)
        if ($this->excludeKey && $value === $this->excludeKey) {
            return;
        }

        if (Registry::key($value)->exists()) {
            $fail('A registry entry with this key already exists.');
        }
    }
}