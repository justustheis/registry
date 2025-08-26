# Laravel Registry

Laravel Registry is a powerful and flexible key–value store designed for managing application settings in Laravel. It offers a unified API with support for per-model scoping, optional encryption, caching, and a modern web interface. The package also supports overriding Laravel configuration values at runtime using registry entries — enabling dynamic, environment-specific configuration.

![Laravel Registry](https://j-theis.de/registry.png)


## ✨ Features

- 🔑 **Flexible Key-Value Storage** - Store any serializable data with dot-notation keys
- 🎯 **Model Scoping** - Scope registry entries to specific Eloquent models
- 🔐 **Encryption Support** - Encrypt sensitive values with Laravel's encryption
- ⚡ **Intelligent Caching** - Built-in Redis/Memcached caching with configurable TTL
- 🎨 **Modern Web Interface** - Vue 3 + Inertia.js frontend with Windows Registry Editor-like UI
- 🔧 **Runtime Config Overrides** - Override Laravel config values dynamically
- 📊 **Type Casting** - Automatic type detection and casting (string, int, float, bool, array, object)
- 🌳 **Hierarchical Keys** - Organize settings in tree structures with parent/child relationships
- 🔍 **Fluent API** - Chainable, expressive syntax for all operations
- 🧪 **Comprehensive Testing** - Full test coverage with PHPUnit
- 🚀 **Performance Optimized** - Efficient queries with proper indexing and caching

## 📋 Requirements

- PHP 8.1+
- Laravel 12+
- Inertia.js 2.0+ (for frontend interface)

## 📦 Installation

### 1. Install via Composer

```bash
composer require justustheis/registry
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --provider="JustusTheis\Registry\RegistryServiceProvider" --tag="config"
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Configure Authorization (Required)

Add the authorization gate to your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

public function boot()
{
    Gate::define('access-registry', function ($user) {
        return $user->isAdmin(); // Customize this logic
    });
}
```

## 🔐 Frontend Access Control

**Important:** Before accessing the registry frontend, you must configure authorization using Laravel Gates. By default, the registry requires users to pass the `access-registry` gate to access the web interface.

### Setting Up Authorization

Add this to your `AppServiceProvider` boot method:

```php
use Illuminate\Support\Facades\Gate;

public function boot()
{
    // Define who can access the registry frontend
    Gate::define('access-registry', function ($user) {
        return $user->isAdmin(); // Adjust this logic as needed
    });
}
```

You can customize the gate name in the configuration:

```php
// config/registry.php
'authorization' => [
    'enabled' => true,
    'gate' => 'access-registry', // Change this to your preferred gate name
],
```

To disable authorization entirely (not recommended for production):

```php
'authorization' => [
    'enabled' => false,
],
```

## ⚙️ Configuration

The configuration file `config/registry.php` provides extensive customization options:

### Cache Configuration

```php
'cache' => [
    'driver' => env('REGISTRY_CACHE_DRIVER', 'redis'),
    'ttl'    => env('REGISTRY_CACHE_TTL', 86400), // 24 hours
],
```

### User Model Configuration

```php
'user_model'       => App\Models\User::class,
'user_name_column' => 'name', // For "updated by" tracking
```

### Model Mappings for Route Binding

```php
'models' => [
    'User' => App\Models\User::class,
    'Product' => App\Models\Product::class,
    // Add your models here for hierarchical key parsing
],
```

### Type Casting Rules

```php
'auto_cast_types' => env('REGISTRY_AUTO_CAST_TYPES', true),
'cast_rules' => [
    'boolean_true_values'  => ['true', 'yes', 'on'],
    'boolean_false_values' => ['false', 'no', 'off'],
    'null_values'          => ['null'],
    'numeric_detection'    => true,
    'array_detection'      => true,
    'object_detection'     => true,
],
```

### Runtime Config Overrides

```php
'override' => [
    'production' => [
        'app.name' => 'app.name', // Maps config('app.name') to registry('app.name')
    ],
    'local' => [
        'mail.default' => 'mail.driver',
    ],
],
```

## 🚀 Basic Usage

### Using the Facade

```php
use JustusTheis\Registry\Facades\Registry;

// Store a value
Registry::key('app.theme')->value('dark')->set();

// Retrieve a value
$theme = Registry::key('app.theme')->default('light')->get();

// Fluent API
$value = Registry::key('user.preferences')
    ->default(['notifications' => true])
    ->encrypt()
    ->set(['notifications' => false, 'theme' => 'dark']);
```

### Using Helper Functions

```php
// Simple get/set operations
registry_set('app.debug', true);
$debug = registry_get('app.debug', false);

// Delete a key
registry_delete('app.debug');

// Flexible registry() helper
$value = registry('app.name', 'Default App Name');
$registry = registry(); // Returns Registry instance
```

### Working with Different Data Types

```php
// String values
Registry::key('app.name')->value('My Application')->set();

// Numeric values
Registry::key('app.version')->value(1.5)->set();

// Boolean values
Registry::key('app.debug')->value(true)->set();

// Arrays and Objects
Registry::key('user.preferences')->value([
    'theme' => 'dark',
    'notifications' => true,
    'sidebar_collapsed' => false
])->set();
```

## 🎯 Model Scoping

Use the `HasRegistry` trait to scope registry entries to specific models:

### Adding the Trait

```php
use JustusTheis\Registry\Traits\HasRegistry;

class User extends Model
{
    use HasRegistry;
}
```

### Scoped Operations

```php
$user = User::find(1);

// Set user-specific settings
$user->registry()->key('preferences.theme')->value('dark')->set();

// Get user-specific settings
$theme = $user->registry()->key('preferences.theme')->default('light')->get();

// Using helper functions with model scoping
registry_set('notifications.email', true, $user);
$emailNotifications = registry_get('notifications.email', false, $user);
```

### Relationship Access

```php
// Access all registry entries for a model
$entries = $user->registryEntries;

// Check if user has specific settings
if ($user->registry()->key('preferences.theme')->exists()) {
    // User has custom theme
}
```

## 🎨 Frontend Web Interface

The package includes a complete Vue 3 + Inertia.js frontend that provides a Windows Registry Editor-like interface.

### Features

- 🌳 **Two-pane layout** - Tree view on left, details on right
- 🔍 **Real-time search** - Filter keys as you type
- ✏️ **In-place editing** - Create, update, delete entries via modals
- 🔐 **Encryption toggle** - Visual indication and control of encrypted entries
- 📱 **Responsive design** - Works on desktop and mobile
- 🎨 **Tailwind CSS** - Modern, clean interface

### Setup

#### 1. Install Frontend Dependencies

```bash
npm install
```

#### 2. Publish Assets (Optional)

```bash
php artisan vendor:publish --provider="JustusTheis\Registry\RegistryServiceProvider" --tag="registry-assets"
```

#### 3. Configure Inertia.js

Ensure you have an Inertia.js app template. The package includes one you can copy:

```bash
cp vendor/justustheis/registry/resources/views/app.blade.php resources/views/app.blade.php
```

#### 4. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### Accessing the Interface

Visit `/registry` in your browser (after setting up the authorization gate).

## 🔐 Encryption

Encrypt sensitive data automatically:

```php
// Encrypt a single value
Registry::key('api.secret')->value('sensitive-data')->encrypt()->set();

// Retrieve encrypted value (automatically decrypted)
$secret = Registry::key('api.secret')->get();

// Using helpers
registry_set('database.password', 'secret123', null, true); // true = encrypt
$password = registry_get('database.password', null, null, true);
```

## 🌳 Hierarchical Keys

Organize settings in tree structures:

```php
// Create hierarchical structure
Registry::key('app.mail.driver')->value('smtp')->set();
Registry::key('app.mail.host')->value('smtp.gmail.com')->set();
Registry::key('app.mail.port')->value(587)->set();

// Work with parent/child relationships
$mailConfig = Registry::key('app.mail.driver');
$parent = $mailConfig->parent(); // Returns 'app.mail' registry
$children = $mailConfig->children(); // Collection of child registries

// Get hierarchical key for display
echo $mailConfig->getHierarchicalKey(); // "app.mail.driver"
```

## ⚡ Advanced Features

### Caching

Registry entries are automatically cached for performance:

```php
// Cache configuration in config/registry.php
'cache' => [
    'driver' => 'redis', // or 'memcached', 'file', etc.
    'ttl'    => 86400,   // 24 hours
],
```

### Runtime Configuration Overrides

Override Laravel config values dynamically:

```php
// In config/registry.php
'override' => [
    'production' => [
        'app.name' => 'app.name', // config('app.name') will check registry first
    ],
],

// Set override value
Registry::key('app.name')->value('Production App')->set();

// Now config('app.name') returns 'Production App'
echo config('app.name'); // "Production App"
```

### Cascade Deletion

Automatically delete registry entries when parent models are deleted:

```php
// In config/registry.php
'cascade_on_delete' => true,
'cascade_on_soft_delete' => false,
```

### Type Validation and Casting

```php
// Explicit type setting
Registry::key('count')->value(42)->type('integer')->set();
Registry::key('data')->value($array)->type('array')->set();

// Automatic type detection (default)
Registry::key('auto')->value('123')->set(); // Stored as integer 123
Registry::key('auto')->value('true')->set(); // Stored as boolean true
```

## 📚 API Reference

### Core Methods

#### `Registry::key(string $key): self`
Set the registry key for the operation.

#### `Registry::value(mixed $value): self`
Set the value to be stored.

#### `Registry::default(mixed $default): self`
Set the default value if key doesn't exist.

#### `Registry::for(Model $model): self`
Scope the registry to a specific model.

#### `Registry::encrypt(): self`
Mark the value for encryption.

#### `Registry::type(string $type): self`
Explicitly set the value type.

### CRUD Operations

#### `get(?string $key = null, mixed $default = null): mixed`
Retrieve a value from the registry.

#### `set(?string $key = null, mixed $value = null): mixed`
Store a value in the registry.

#### `delete(?string $key = null): bool`
Delete a key from the registry.

#### `exists(?string $key = null): bool`
Check if a key exists in the registry.

#### `rename(string $newKey, bool $renameChildren = true): self`
Rename a registry key and optionally its children.

### Relationship Methods

#### `parent(): ?self`
Get the parent registry instance.

#### `children(): Collection`
Get all direct child registry instances.

## 💡 Usage Examples

### User Preferences System

```php
class User extends Model
{
    use HasRegistry;
}

// Set user preferences
$user = User::find(1);
$user->registry()->key('preferences')->value([
    'theme' => 'dark',
    'language' => 'en',
    'notifications' => [
        'email' => true,
        'push' => false,
        'sms' => false
    ]
])->set();

// Get specific preference
$theme = $user->registry()->key('preferences.theme')->default('light')->get();

// Update single preference
$user->registry()->key('preferences.language')->value('es')->set();
```

### Application Configuration

```php
// Environment-specific settings
if (app()->environment('production')) {
    Registry::key('app.debug')->value(false)->set();
    Registry::key('cache.default')->value('redis')->set();
} else {
    Registry::key('app.debug')->value(true)->set();
    Registry::key('cache.default')->value('file')->set();
}

// API configuration with encryption
Registry::key('services.stripe.secret')
    ->value('sk_test_...')
    ->encrypt()
    ->set();
```

### Feature Flags

```php
// Global feature flags
Registry::key('features.new_dashboard')->value(true)->set();
Registry::key('features.beta_search')->value(false)->set();

// User-specific feature overrides
$user->registry()->key('features.beta_search')->value(true)->set();

// Check feature availability
function hasFeature($feature, $user = null) {
    if ($user && $user->registry()->key("features.{$feature}")->exists()) {
        return $user->registry()->key("features.{$feature}")->get();
    }
    
    return registry_get("features.{$feature}", false);
}
```

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/

# Run specific test categories
vendor/bin/phpunit --filter RegistryCache
vendor/bin/phpunit --filter RegistryEncryption
vendor/bin/phpunit --filter RegistryScope
```

## 🔧 Artisan Commands

The package doesn't include specific Artisan commands, but you can interact with it using Tinker:

```bash
php artisan tinker

# Try some commands
Registry::key('test')->value('Hello World')->set()
Registry::key('test')->get()
registry('test', 'Default Value')
```

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🤝 Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 🐛 Issues

If you discover any security vulnerabilities or bugs, please report them via [GitHub Issues](https://github.com/justustheis/registry/issues).

## 📞 Support

For support, please create an issue on GitHub or contact the maintainer at [justus@sharma-theis.com](mailto:justus@sharma-theis.com).
