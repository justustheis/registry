<?php

namespace JustusTheis\Registry\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use JustusTheis\Registry\Services\RegistryEncryption;
use JustusTheis\Registry\Services\RegistryTypeCaster;

/**
 * @property string      $key
 * @property mixed       $value
 * @property string      $type
 * @property string|null $registrable_type
 * @property int|null    $registrable_id
 * @property int|null    $updated_by
 * @property bool        $encrypted
 */
class RegistryEntry extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Registry Entry Model
    |--------------------------------------------------------------------------
    |
    | Represents a single entry stored in the registry. Entries may optionally
    | be scoped to a model via the polymorphic registrable relation.
    |
    */

    /**
     * Table containing registry entries.
     *
     * @var string
     */
    protected $table = 'registries';

    /**
     * Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'registrable_type',
        'registrable_id',
        'type',
        'encrypted',
        'updated_by',
    ];

    /**
     * Attribute casting configuration.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'encrypted' => 'boolean',
    ];

    /**
     * Automatically cast the value based on the type field.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        if ($this->encrypted) {
            $value = RegistryEncryption::decrypt($value);
        }

        return RegistryTypeCaster::cast($value, $this->type);
    }

    /**
     * Relationship to the owning model, if any.
     *
     * @return MorphTo<Model, RegistryEntry>
     */
    public function registrable(): MorphTo
    {
        /** @var MorphTo<Model, RegistryEntry> $relation */
        $relation = $this->morphTo();

        return $relation;
    }

    /**
     * Scope query to global entries only.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('registrable_type')
                     ->whereNull('registrable_id');
    }

    /**
     * Scope query to entries for a specific model.
     *
     * @param  Builder<self> $query
     * @param  Model         $model
     * @return Builder<self>
     */
    public function scopeForModel(Builder $query, Model $model): Builder
    {
        return $query->where('registrable_type', get_class($model))
                     ->where('registrable_id', $model->getKey());
    }

    /**
     * Scope query to entries for a specific model type.
     *
     * @param  Builder<self> $query
     * @param  string        $modelType
     * @return Builder<self>
     */
    public function scopeForModelType(Builder $query, string $modelType): Builder
    {
        return $query->where('registrable_type', $modelType);
    }

    /**
     * Scope query to entries with a specific key.
     *
     * @param  Builder<self> $query
     * @param  string        $key
     * @return Builder<self>
     */
    public function scopeWithKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    /**
     * Scope query to encrypted entries only.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeEncrypted(Builder $query): Builder
    {
        return $query->where('encrypted', true);
    }

    /**
     * Get a registry value for a specific key and optional model.
     *
     * @param  string     $key
     * @param  Model|null $model
     * @return mixed
     */
    public static function getValue(string $key, ?Model $model = null)
    {
        $entry = static::findPair($key, $model);

        return $entry ? $entry->value : null;
    }

    /**
     * Get a registry entrey for a specific key and optional model.
     *
     * @param  string     $key
     * @param  Model|null $model
     * @return self
     */
    public static function findPair(string $key, ?Model $model = null) :self|null
    {
        $query = static::withKey($key);
        $query = $model ? $query->forModel($model) : $query->global();

        return $query->first();
    }

    /**
     * Get a registry entrey for a specific key with optional model, then delete it.
     *
     * @param  string     $key
     * @param  Model|null $model
     * @return bool
     */
    public static function findPairAndDelete(string $key, ?Model $model = null) :bool
    {
        $entry = self::findPair($key, $model);
        if ($entry) {
            return (bool) $entry->delete();
        }

        return false;
    }
}
