<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | This option controls the cache driver that will be used for caching
    | registry entries. Supported: "redis", "memcached", "file", etc.
    |
    */

    'cache' => [
        'driver' => env('REGISTRY_CACHE_DRIVER', 'redis'),
        'ttl'    => env('REGISTRY_CACHE_TTL', 86400),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model Configuration
    |--------------------------------------------------------------------------
    |
    | These options control how the "updated by" information is resolved. You
    | may customize the user model class and the column that should be used
    | to display the user's name in the UI.
    |
    */

    'user_model'       => App\Models\User::class,
    'user_name_column' => 'name',

    /*
    |--------------------------------------------------------------------------
    | Model Mappings for Route Binding
    |--------------------------------------------------------------------------
    |
    | Define model name mappings for hierarchical key parsing. This allows
    | the registry to correctly parse scoped keys like "User.123.settings.theme"
    | into model instances. Add your application's models here.
    |
    | Example: 'User' => App\Models\User::class
    |
    */

    'models' => [
        // 'User' => App\Models\User::class,
        // 'Product' => App\Models\Product::class,
        // 'Order' => App\Models\Order::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization Bypass Environments
    |--------------------------------------------------------------------------
    |
    | Authorization checks are skipped when the application is running in any
    | of the listed environments. This is handy for local development or
    | automated testing where gate checks would otherwise interfere.
    |
    */

    'bypass_authorization_envs' => ['local'],

    /*
    |--------------------------------------------------------------------------
    | Frontend Authorization
    |--------------------------------------------------------------------------
    |
    | Configure access control for the registry frontend interface. When
    | enabled, users must pass the specified gate to access registry routes.
    | Define the gate in your AppServiceProvider's boot method.
    |
    | Example gate definition:
    | Gate::define('access-registry', fn($user) => $user->isAdmin());
    |
    */

    'authorization' => [
        'enabled' => env('REGISTRY_AUTHORIZATION_ENABLED', true),
        'gate'    => env('REGISTRY_AUTHORIZATION_GATE', 'access-registry'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Automatic Type Casting
    |--------------------------------------------------------------------------
    |
    | Controls automatic type casting of registry values during retrieval.
    | When enabled, values are automatically cast to appropriate types:
    | - '123' becomes 123 (integer)
    | - '12.34' becomes 12.34 (float)
    | - 'true'/'false' becomes true/false (boolean)
    | - 'null' becomes null
    |
    */

    'auto_cast_types' => env('REGISTRY_AUTO_CAST_TYPES', true),

    /*
    |--------------------------------------------------------------------------
    | Type Casting Rules
    |--------------------------------------------------------------------------
    |
    | Configure specific type casting behavior. You can customize which
    | string values should be considered as booleans and how numeric
    | detection should work.
    |
    */

    'cast_rules' => [
        'boolean_true_values'  => ['true', 'yes', 'on'],
        'boolean_false_values' => ['false', 'no', 'off'],
        'null_values'          => ['null'],
        'numeric_detection'    => env('REGISTRY_NUMERIC_DETECTION', true),
        'array_detection'      => env('REGISTRY_ARRAY_DETECTION', true),
        'object_detection'     => env('REGISTRY_OBJECT_DETECTION', true),
        'strict_boolean_mode'  => env('REGISTRY_STRICT_BOOLEAN_MODE', true), // When true, '0' and '1' are treated as numbers
    ],

    /*
    |--------------------------------------------------------------------------
    | Cascade Delete Configuration
    |--------------------------------------------------------------------------
    |
    | When enabled, entries scoped to models will be automatically deleted
    | when the parent model is deleted. This helps maintain referential
    | integrity but can result in data loss. Be cautious.
    |
    | - cascade_on_delete: Controls whether to cascade delete registry entries
    | - cascade_on_soft_delete: Controls whether to cascade on soft deletes
    |   (only applies when cascade_on_delete is true)
    |
    */

    'cascade_on_delete'      => env('REGISTRY_CASCADE_ON_DELETE', false),
    'cascade_on_soft_delete' => env('REGISTRY_CASCADE_ON_SOFT_DELETE', false),

    /*
    |--------------------------------------------------------------------------
    | Environment-Specific Overrides
    |--------------------------------------------------------------------------
    |
    | Use the registry to override Laravel configuration values at runtime.
    | If a key exists in the registry, it will replace the config. For
    | example, config('app.name') normally returns "Laravel", but
    | with an override entry 'app.name' => 'MyApp' stored
    | in the registry, you'll get "MyApp" instead.
    |
    */

    'overrides' => [

        'production' => [
            // 'app.name' => 'app.name',
        ],

        'local' => [
            // 'app.name' => 'app.name',
        ],

        'testing' => [
            // 'app.name' => 'app.name',
        ],

    ],

];
