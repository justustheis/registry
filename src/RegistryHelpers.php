<?php

/*
|--------------------------------------------------------------------------
| Registry Helper Functions
|--------------------------------------------------------------------------
|
| Provides convenient global helper functions for interacting with the
| registry system including the main registry() function for
| easy access throughout applications.
|
*/

use JustusTheis\Registry\Registry;
use Illuminate\Database\Eloquent\Model;
use JustusTheis\Registry\Facades\Registry as RegistryFacade;

if (! function_exists('registry')) {
    /**
     * Access the registry system with flexible API.
     *
     * Usage patterns:
     * - registry() - Returns a Registry instance
     * - registry($key, $default) - Get global registry value
     * - registry($key, $default, $model) - Get scoped registry value
     *
     * @param  string|null    $key
     * @param  mixed          $default
     * @param  Model|null     $model
     * @param  bool           $encrypted
     * @return mixed|Registry
     */
    function registry($key = null, $default = null, $model = null, bool $encrypted = false, string $type = '')
    {
        if ($key === null) {
            return new Registry();
        }

        $registry = RegistryFacade::key($key)->default($default)->encryption($encrypted)->type($type);

        if ($model !== null) {
            $registry = $registry->for($model);
        }

        return $registry->get();
    }
}

if (! function_exists('registry_get')) {
    /**
     * Get a value from the registry.
     *
     * @param  string     $key       The registry key
     * @param  mixed      $default   The default value if key is not found
     * @param  Model|null $model     Optional model to scope the registry to
     * @param  bool       $encrypted
     * @return mixed      The registry value or default
     */
    function registry_get(string $key, $default = null, $model = null, bool $encrypted = false)
    {
        $registry = RegistryFacade::key($key)->default($default)->encryption(false);

        if ($model !== null) {
            $registry = $registry->for($model);
        }

        return $registry->get();
    }
}

if (! function_exists('registry_set')) {
    /**
     * Set a value in the registry.
     *
     * @param  string     $key       The registry key
     * @param  mixed      $value     The value to store
     * @param  Model|null $model     Optional model to scope the registry to
     * @param  bool       $encrypted Whether to encrypt the value
     * @return mixed      The stored value
     */
    function registry_set(string $key, $value, $model = null, bool $encrypted = false)
    {
        $registry = RegistryFacade::key($key)->value($value)->encryption($encrypted);

        if ($model !== null) {
            $registry = $registry->for($model);
        }

        return $registry->set();
    }
}

if (! function_exists('registry_delete')) {
    /**
     * Delete a value from the registry.
     *
     * @param  string     $key   The registry key
     * @param  mixed|null $model Optional model to scope the registry to
     * @return bool       True if the value was deleted, false otherwise
     */
    function registry_delete(string $key, $model = null): bool
    {
        $registry = RegistryFacade::key($key);

        if ($model !== null) {
            $registry = $registry->for($model);
        }

        return $registry->delete();
    }
}

if (! function_exists('registry_filter')) {
    /**
     * Filter registry entries by a pattern.
     *
     * @param  string                                $pattern Pattern with % as wildcard (e.g., 'app.settings.%')
     * @param  Model|null                            $model   Optional model to scope the registry to
     * @return \Illuminate\Support\Collection<string, mixed> Collection keyed by registry key with values
     */
    function registry_filter(string $pattern, $model = null): \Illuminate\Support\Collection
    {
        $registry = new Registry();

        if ($model !== null) {
            $registry = $registry->for($model);
        }

        return $registry->filter($pattern);
    }
}
