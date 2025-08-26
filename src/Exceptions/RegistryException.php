<?php

namespace JustusTheis\Registry\Exceptions;

use Exception;

abstract class RegistryException extends Exception
{
    /*
    |--------------------------------------------------------------------------
    | Registry Base Exception
    |--------------------------------------------------------------------------
    |
    | This abstract class serves as the base for all registry-related
    | exceptions. It provides common functionality for handling registry
    | errors including key context and logging support.
    |
    */

    /**
     * The registry key that caused the error, if applicable.
     *
     * @var string|null
     */
    protected ?string $registryKey = null;

    /**
     * Set the registry key that caused the error.
     *
     * This method allows chaining and provides context about which
     * registry key was involved in the error for better debugging.
     *
     * @param  string $key The registry key that caused the error
     * @return static The exception instance for method chaining
     */
    public function setRegistryKey(?string $key = null): static
    {
        $this->registryKey = $key;

        return $this;
    }

    /**
     * Get the registry key that caused the error.
     *
     * Returns the registry key associated with this exception, or null
     * if no specific key was involved in the error.
     *
     * @return string|null The registry key or null if not applicable
     */
    public function getRegistryKey(): ?string
    {
        return $this->registryKey;
    }

    /**
     * Get the error context for logging and debugging purposes.
     *
     * Returns an array of contextual information about the error,
     * including the registry key if available. This is useful for
     * structured logging and error reporting.
     *
     * @return array<mixed> Array of context data for logging
     */
    public function getContext(): array
    {
        $context = [];

        if ($this->registryKey) {
            $context['registry_key'] = $this->registryKey;
        }

        return $context;
    }
}
