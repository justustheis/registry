<?php

namespace JustusTheis\Registry\Facades;

use Illuminate\Support\Facades\Facade;
use JustusTheis\Registry\Registry as RegistryBuilder;

class Registry extends Facade
{
    /*
    |--------------------------------------------------------------------------
    | Registry Facade
    |--------------------------------------------------------------------------
    |
    | This facade provides a convenient static interface to the Registry
    | builder. It proxies all calls to a RegistryBuilder instance, providing
    | clean, expressive syntax throughout Laravel applications.
    |
    */

    /**
     * Get the registered name of the component.
     *
     * This method returns the service container binding name that Laravel
     * uses to resolve the underlying RegistryBuilder service instance.
     *
     * @return string The service container binding name
     */
    protected static function getFacadeAccessor(): string
    {
        self::clearResolvedInstance(RegistryBuilder::class);

        return RegistryBuilder::class;
    }
}
