<?php

namespace JustusTheis\Registry;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use JustusTheis\Registry\Models\RegistryEntry;
use JustusTheis\Registry\Services\RegistryEncryption;
use JustusTheis\Registry\Services\RegistryKeyValidator;
use JustusTheis\Registry\Services\RegistryValueValidator;
use JustusTheis\Registry\Services\RegistryModelScopeValidator;

class Registry
{
    /*
    |--------------------------------------------------------------------------
    | Registry
    |--------------------------------------------------------------------------
    |
    | Service class used to read and write values from the registry. All
    | persistence logic is delegated to the configured store.
    |
    */

    /**
     * The model instance to scope registry operations to.
     */
    protected ?Model $scopedTo = null;

    /**
     * The registry key for this instance.
     */
    protected string $key;

    /**
     * The default value to return when the key is not found.
     */
    protected mixed $default = null;

    /**
     * The value to be stored in the registry.
     */
    protected mixed $value = null;

    /**
     * The value type to be used.
     */
    protected ?string $type = null;

    /**
     * Whether the value should be encrypted.
     */
    protected bool $encrypted = false;

    /**
     * Create a scoped registry instance for the given model.
     *
     * @param  Model                                  $model
     * @return self
     * @throws Exceptions\RegistryValidationException
     */
    public function for(Model $model): self
    {
        $instance = clone $this;
        $instance->scopedTo = RegistryModelScopeValidator::handle($model);

        return $instance;
    }

    /**
     * Sets the key for this instance.
     *
     * @param  string                                 $key The registry key to set
     * @return self
     * @throws Exceptions\RegistryValidationException
     */
    public function key(string $key): self
    {
        $this->key = RegistryKeyValidator::handle($key);

        return $this;
    }

    /**
     * Sets the value for this instance.
     *
     * @param  mixed                                  $value The value to store in the registry
     * @return self
     * @throws Exceptions\RegistryValidationException
     */
    public function value($value): self
    {
        $this->value = RegistryValueValidator::handle($value);

        return $this;
    }

    /**
     * Sets the value type for this instance.
     *
     * @param  string|null $type
     * @return self
     */
    public function type(string|null $type): self
    {
        $this->type = $type == '' ? null : $type;

        return $this;
    }

    /**
     * Sets the default value for this instance.
     *
     * @param  mixed                                  $default The default value to return when key is not found
     * @return self
     * @throws Exceptions\RegistryValidationException
     */
    public function default($default): self
    {
        $this->default = RegistryValueValidator::handle($default);

        return $this;
    }

    /**
     * Sets the encryption type for this instance.
     *
     * @param  bool $encryption
     * @return self
     */
    public function encryption(bool $encryption): self
    {
        $this->encrypted = $encryption;

        return $this;
    }

    /**
     * Mark this registry entry for encryption.
     *
     * @return self
     */
    public function encrypt(): self
    {
        return $this->encryption(true);
    }

    /**
     * Retrieve the value for the specified key.
     *
     * @param string|null $key     The registry key (optional if already set)
     * @param mixed       $default The default value (optional if already set)
     *
     * @throws Exceptions\RegistryValidationException
     *
     * @return mixed The retrieved value or default
     */
    public function get(?string $key = null, $default = null, ?string $type = null, ?bool $encrypted = null)
    {
        $key && $this->key($key);
        $default !== null && $this->default($default);
        $type !== null && $this->type($type);
        $encrypted !== null && $this->encryption($encrypted);

        $valueEntry = Cache::memo(config('registry.cache.driver'))->remember($this->getCacheKey(), config('registry.cache.ttl'), function () {
            $entry = RegistryEntry::findPair($this->key, $this->scopedTo);

            if ($entry === null) {
                $value = $this->default !== null ? $this->value($this->default)->set() : null;
                $encrypted = false;
            } else {
                $value = $entry->encrypted ? RegistryEncryption::encrypt($entry->value) : $entry->value;
                $encrypted = $entry->encrypted;
            }

            return [
                'encrypted' => $encrypted,
                'value'     => $value,
            ];
        });

        return $valueEntry['encrypted']
            ? RegistryEncryption::decrypt($valueEntry['value'])
            : $valueEntry['value'];
    }

    /**
     * Persist the given value at the specified key.
     *
     * @param string|null $key       The registry key (optional if already set)
     * @param mixed       $value     The value to store (optional if already set)
     * @param bool        $encrypted Whether to encrypt the value
     * @param string|null $type      The value type for storage (null for auto-detection)
     *
     * @throws Exceptions\RegistryValidationException
     *
     *
     * @return mixed The stored value
     */
    public function set(?string $key = null, $value = null, ?bool $encrypted = null, ?string $type = null)
    {
        $key && $this->key($key);
        $encrypted !== null && $this->encryption($encrypted);
        $type !== null && $this->type($type);
        $value !== null && $this->value($value);
        $this->encrypted && $this->value(RegistryEncryption::encrypt($this->value));

        RegistryEntry::updateOrCreate(
            [
                'key'              => $this->key,
                'registrable_type' => $this->scopedTo ? get_class($this->scopedTo) : null,
                'registrable_id'   => $this->scopedTo ? $this->scopedTo->getKey() : null,
            ],
            [
                'value'     => $this->value,
                'type'      => $this->type,
                'encrypted' => $this->encrypted,
            ]
        );

        Cache::memo(config('registry.cache.driver'))->forget($this->getCacheKey());

        return $this->get();
    }

    /**
     * Delete the given key from storage.
     *
     * @param string|null $key The registry key (optional if already set)
     *
     * @return bool
     */
    public function delete(?string $key = null): bool
    {
        $key && $this->key($key);
        Cache::memo(config('registry.cache.driver'))->forget($this->getCacheKey());

        return RegistryEntry::findPairAndDelete($this->key, $this->scopedTo);
    }

    /**
     * Rename the current key to a new key.
     *
     * @param string $newKey         The new key name
     * @param bool   $renameChildren Whether to rename child keys as well
     *
     * @return self
     */
    public function rename(string $newKey, bool $renameChildren = true)
    {
        $entry = RegistryEntry::findPair($this->key, $this->scopedTo);

        if (! $entry) {
            return false;
        }
        $oldKey = $this->key;
        $entry->update(['key' => $newKey]);

        if ($renameChildren) {
            $childEntries = RegistryEntry::where('key', 'LIKE', $oldKey.'.%')
                ->where('registrable_type', $this->scopedTo ? get_class($this->scopedTo) : null)
                ->where('registrable_id', $this->scopedTo ? $this->scopedTo->getKey() : null)
                ->get();

            foreach ($childEntries as $childEntry) {
                // Replace the old key prefix with the new key prefix
                $newChildKey = $newKey.substr($childEntry->key, strlen($oldKey));
                $childEntry->update(['key' => $newChildKey]);
            }
        }
        $this->key = $newKey;

        return $this;
    }

    /**
     * Check if the given key exists in storage.
     *
     * @param string|null $key The registry key (optional if already set)
     *
     * @return bool
     */
    public function exists(?string $key = null): bool
    {
        $key && $this->key($key);

        $entry = RegistryEntry::findPair($this->key, $this->scopedTo);

        return $entry !== null;
    }

    /**
     * Get the current registry key.
     *
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key ?? null;
    }

    /**
     * Get the scoped model instance.
     *
     * @return Model|null
     */
    public function getScopedModel(): ?Model
    {
        return $this->scopedTo;
    }

    /**
     * Get the hierarchical key including scope information for display.
     *
     * @return string The hierarchical key (e.g., "ModelName.1.key.path" or "key.path")
     */
    public function getHierarchicalKey(): string
    {
        if ($this->scopedTo) {
            $modelName = class_basename(get_class($this->scopedTo));

            return "{$modelName}.{$this->scopedTo->getKey()}.{$this->key}";
        }

        return $this->key;
    }

    /**
     * Get the parent registry instance using hierarchical scope preference.
     * First tries same scope as child, then falls back to global scope.
     *
     * @return Registry|null Returns null if this is a root level key or no parent exists
     */
    public function parent(): ?self
    {
        $keyParts = explode('.', $this->key);

        if (count($keyParts) <= 1) {
            return null; // Root level key has no parent
        }

        $parentKey = implode('.', array_slice($keyParts, 0, -1));

        // Look up parent with hierarchical scope preference:
        // 1. Same scope as current registry
        // 2. Global scope (no scope)
        $parentEntry = RegistryEntry::where('key', $parentKey)
            ->orderByRaw('CASE
                WHEN registrable_type = ? AND registrable_id = ? THEN 1
                WHEN registrable_type IS NULL AND registrable_id IS NULL THEN 2
                ELSE 3
            END', [
                $this->scopedTo ? get_class($this->scopedTo) : null,
                $this->scopedTo ? $this->scopedTo->getKey() : null,
            ])
            ->first();

        if (! $parentEntry) {
            return null; // No parent found
        }

        // Create Registry with the parent's actual scope
        $registry = new static();
        if ($parentEntry->registrable_type && $parentEntry->registrable_id) {
            $scopedModel = $parentEntry->registrable_type::find($parentEntry->registrable_id);
            if ($scopedModel) {
                $registry = $registry->for($scopedModel);
            }
        }

        return $registry->key($parentKey);
    }

    /**
     * Get all direct child registry instances.
     *
     * @return Collection<Registry> Collection of child Registry instances
     */
    public function children(): Collection
    {
        $childPrefix = $this->key.'.';

        $entries = RegistryEntry::where('registrable_type', $this->scopedTo ? get_class($this->scopedTo) : null)
            ->where('registrable_id', $this->scopedTo ? $this->scopedTo->getKey() : null)
            ->where('key', 'LIKE', $childPrefix.'%')
            ->get();

        return $entries->filter(function ($entry) use ($childPrefix) {
            // Only direct children (no additional dots after the prefix)
            $remainingKey = substr($entry->key, strlen($childPrefix));

            return ! str_contains($remainingKey, '.');
        })->map(function ($entry) {
            $registry = new static();
            if ($this->scopedTo) {
                $registry = $registry->for($this->scopedTo);
            }

            return $registry->key($entry->key);
        })->values();
    }

    /**
     * Filter registry entries by a pattern and return their values.
     *
     * @param  string                     $pattern Pattern with % as wildcard (e.g., 'radius.mayByPass.%')
     * @return Collection<string, mixed> Collection keyed by registry key with values
     */
    public function filter(string $pattern): Collection
    {
        $entries = RegistryEntry::where('registrable_type', $this->scopedTo ? get_class($this->scopedTo) : null)
            ->where('registrable_id', $this->scopedTo ? $this->scopedTo->getKey() : null)
            ->where('key', 'LIKE', $pattern)
            ->get();

        return $entries->mapWithKeys(function ($entry) {
            return [$entry->key => $entry->value];
        });
    }

    /**
     * Generate a unique cache key for the current registry configuration.
     *
     * @return string The cache key
     */
    protected function getCacheKey(): string
    {
        return sprintf(
            '%s$%s:%s#%s',
            'RegistryEntry',
            $this->scopedTo ? get_class($this->scopedTo) : 'global',
            $this->scopedTo ? $this->scopedTo->getKey() : '0',
            $this->key,
        );
    }
}
