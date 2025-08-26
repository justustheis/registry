<?php

namespace JustusTheis\Registry\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use JustusTheis\Registry\Models\RegistryEntry;

class TestRegistryFactory
{
    /**
     * Create a global registry entry.
     *
     * @param  string        $key
     * @param  mixed         $value
     * @param  array         $attributes Additional attributes (type, encrypted, etc.)
     * @return RegistryEntry
     */
    public static function createGlobal(string $key = 'some.key', $value = 'some value', array $attributes = []): RegistryEntry
    {
        return RegistryEntry::create(array_merge([
            'key'              => $key,
            'value'            => $value,
            'registrable_type' => null,
            'registrable_id'   => null,
        ], $attributes));
    }

    /**
     * Create a scoped registry entry for a model.
     *
     * @param  string        $key
     * @param  mixed         $value
     * @param  Model         $model
     * @param  array         $attributes Additional attributes (type, encrypted, etc.)
     * @return RegistryEntry
     */
    public static function createScoped(string $key = 'some.key', $value = 'some value', ?Model $model = null, array $attributes = []): RegistryEntry
    {
        if (! $model) {
            $model = TestUserFactory::create();
        }

        return RegistryEntry::create(array_merge([
            'key'              => $key,
            'value'            => $value,
            'registrable_type' => get_class($model),
            'registrable_id'   => $model->getKey(),
        ], $attributes));
    }

    /**
     * Create multiple global registry entries.
     *
     * @param  array $entries    Array of ['key' => 'value'] pairs
     * @param  array $attributes Additional attributes for all entries
     * @return array Array of created RegistryEntry models
     */
    public static function createGlobalBatch(array $entries, array $attributes = []): array
    {
        $created = [];

        foreach ($entries as $key => $value) {
            $created[$key] = self::createGlobal($key, $value, $attributes);
        }

        return $created;
    }

    /**
     * Create multiple scoped registry entries for a model.
     *
     * @param  array $entries    Array of ['key' => 'value'] pairs
     * @param  Model $model
     * @param  array $attributes Additional attributes for all entries
     * @return array Array of created RegistryEntry models
     */
    public static function createScopedBatch(array $entries, Model $model, array $attributes = []): array
    {
        $created = [];

        foreach ($entries as $key => $value) {
            $created[$key] = self::createScoped($key, $value, $model, $attributes);
        }

        return $created;
    }
}
