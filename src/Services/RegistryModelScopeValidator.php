<?php

namespace JustusTheis\Registry\Services;

use Illuminate\Database\Eloquent\Model;
use JustusTheis\Registry\Traits\HasRegistry;
use JustusTheis\Registry\Exceptions\RegistryValidationException;

class RegistryModelScopeValidator
{
    /*
    |--------------------------------------------------------------------------
    | Registry Model Scope Validator Service
    |--------------------------------------------------------------------------
    |
    | Validates that Eloquent models use the HasRegistry trait before allowing
    | them to be used with registry scoping functionality ensuring
    | proper trait implementation.
    |
    */

    /**
     * Validate that a model uses the HasRegistry trait.
     *
     * @param  Model                       $model The model instance to validate
     * @throws RegistryValidationException If model doesn't use HasRegistry trait
     * @return Model                       The validated model instance
     */
    public static function handle(Model $model) :Model
    {
        $modelClass = get_class($model);

        if (! in_array(HasRegistry::class, class_uses_recursive($modelClass))) {
            throw RegistryValidationException::invalidModel($modelClass);
        }

        return $model;
    }
}
