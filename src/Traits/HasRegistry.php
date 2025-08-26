<?php

namespace JustusTheis\Registry\Traits;

use JustusTheis\Registry\Facades\Registry;
use JustusTheis\Registry\Models\RegistryEntry;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasRegistry
{
    /*
    |--------------------------------------------------------------------------
    | Has Registry Trait
    |--------------------------------------------------------------------------
    |
    | Adds convenient accessors for interacting with the registry from an
    | Eloquent model. Models using this trait can easily retrieve a scoped
    | Registry instance and access related entries.
    |
    */

    /**
     * Boot the HasRegistry trait for the model.
     */
    public static function bootHasRegistry(): void
    {
        static::deleting(function ($model) {
            if (! config('registry.cascade_on_delete', false) || ! $model->isCascadingDeletesToRegistry) {
                return;
            }

            $model->deleteAllRegistryEntries();
        });
    }

    /**
     * Retrieve a Registry instance scoped to this model.
     */
    public function registry(): \JustusTheis\Registry\Registry
    {
        return Registry::for($this);
    }

    /**
     * Get all of the registrie entries.
     */
    public function registryEntries(): MorphMany
    {
        return $this->morphMany(RegistryEntry::class, 'registrable');
    }

    public function getIsCascadingDeletesToRegistryAttribute()
    {
        $forceDeleting = method_exists($this, 'isForceDeleting') ? $this->isForceDeleting() : null;

        return $forceDeleting === true ||
            ($forceDeleting === false && config('registry.cascade_on_soft_delete', false)) ||
            $forceDeleting === null;
    }

    /**
     * Delete registry entries.
     */
    protected function deleteAllRegistryEntries(): void
    {
        foreach ($this->registryEntries as $registryEntry) {
            $this->registry()->key($registryEntry->key)->delete();
        }
    }
}
