<?php

namespace JustusTheis\Registry\Http\Bindings;

use Illuminate\Routing\Route;
use JustusTheis\Registry\Registry;
use Illuminate\Database\Eloquent\Model;
use JustusTheis\Registry\Exceptions\RegistryException;

class RegistryKeyBinding
{
    /*
    |--------------------------------------------------------------------------
    | Registry Key Binding Service
    |--------------------------------------------------------------------------
    |
    | Resolves hierarchical registry keys from route parameters, supporting
    | both global and model-scoped keys with automatic model resolution
    | and URL decoding for seamless route binding.
    |
    */

    /**
     * Resolve a registry key from the route parameter.
     *
     * @param  string            $value The hierarchical key from the route
     * @param  Route             $route The route instance
     * @return Registry
     * @throws RegistryException
     */
    public function __invoke(string $value, Route $route): Registry
    {
        // Decode URL-encoded key
        $hierarchicalKey = urldecode($value);

        // Parse the hierarchical key
        $parsedKey = $this->parseHierarchicalKey($hierarchicalKey);

        // Create a new Registry instance
        $registry = app(Registry::class);

        // If the key is scoped, set the scope
        if ($parsedKey['model']) {
            $registry = $registry->for($parsedKey['model']);
        }

        // Set the registry key
        $registry = $registry->key($parsedKey['key']);

        return $registry;
    }

    /**
     * Parse a hierarchical key into its components.
     *
     * @param  string                                $hierarchicalKey
     * @return array{model: Model|null, key: string}
     * @throws RegistryException
     */
    protected function parseHierarchicalKey(string $hierarchicalKey): array
    {
        $parts = explode('.', $hierarchicalKey);

        // Need at least 3 parts for a scoped key: ModelName.ID.key
        if (count($parts) < 3) {
            return [
                'model' => null,
                'key'   => $hierarchicalKey,
            ];
        }

        $possibleModelName = $parts[0];
        $possibleModelId = $parts[1];

        // Check if first part is a configured model name and second part is numeric
        if ($this->isConfiguredModel($possibleModelName) && is_numeric($possibleModelId)) {
            // This is a scoped key
            $registryKey = implode('.', array_slice($parts, 2));
            $model = $this->resolveModel($possibleModelName, $possibleModelId);

            return [
                'model' => $model,
                'key'   => $registryKey,
            ];
        }

        // Try dynamic resolution as fallback
        if ($this->isPossibleModelName($possibleModelName) && is_numeric($possibleModelId)) {
            $model = $this->tryDynamicModelResolution($possibleModelName, $possibleModelId);

            if ($model) {
                $registryKey = implode('.', array_slice($parts, 2));

                return [
                    'model' => $model,
                    'key'   => $registryKey,
                ];
            }
        }

        // Not a scoped key, treat entire string as global key
        return [
            'model' => null,
            'key'   => $hierarchicalKey,
        ];
    }

    /**
     * Check if a model name is configured in the models config.
     *
     * @param  string $modelName
     * @return bool
     */
    protected function isConfiguredModel(string $modelName): bool
    {
        $models = config('registry.models', []);

        return isset($models[$modelName]);
    }

    /**
     * Check if a string could be a model name (PascalCase).
     *
     * @param  string $possibleModelName
     * @return bool
     */
    protected function isPossibleModelName(string $possibleModelName): bool
    {
        // Check if it starts with uppercase and contains only letters/numbers
        return ctype_upper($possibleModelName[0]) && ctype_alnum($possibleModelName);
    }

    /**
     * Try to dynamically resolve a model by attempting common namespaces.
     *
     * @param  string     $modelName
     * @param  string     $modelId
     * @return Model|null
     */
    protected function tryDynamicModelResolution(string $modelName, string $modelId): ?Model
    {
        $commonNamespaces = [
            "App\\Models\\{$modelName}",
            "App\\{$modelName}",
            $modelName, // In case it's already fully qualified
        ];

        foreach ($commonNamespaces as $className) {
            if (class_exists($className) && is_subclass_of($className, Model::class)) {
                try {
                    $model = $className::find($modelId);
                    if ($model) {
                        return $model;
                    }
                } catch (\Exception $e) {
                    // Continue to next namespace
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * Resolve a model instance from configured model mappings.
     *
     * @param  string            $modelName
     * @param  string            $modelId
     * @return Model
     * @throws RegistryException
     */
    protected function resolveModel(string $modelName, string $modelId): Model
    {
        $models = config('registry.models', []);
        $modelClass = $models[$modelName];

        // Find the model instance
        $model = $modelClass::find($modelId);

        if (! $model) {
            throw new RegistryException("Model '{$modelClass}' with ID '{$modelId}' not found");
        }

        return $model;
    }
}
