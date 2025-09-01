<?php

namespace JustusTheis\Registry;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use JustusTheis\Registry\Facades\Registry;
use JustusTheis\Registry\Http\Bindings\RegistryKeyBinding;

class RegistryServiceProvider extends ServiceProvider
{
    /*
    |--------------------------------------------------------------------------
    | Registry Service Provider
    |--------------------------------------------------------------------------
    |
    | Service provider responsible for bootstrapping the registry.
    |
    */

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/registry.php', 'registry');
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');

        $this->publishes([
            __DIR__.'/../config/registry.php' => config_path('registry.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/justustheis/registry'),
        ], 'public');

        require_once __DIR__.'/RegistryHelpers.php';

        // Register custom route model binding for registry keys
        $this->registerRouteModelBinding();

        $this->loadRoutesFrom(__DIR__.'/../routes/registry.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'registry');

        $this->applyConfigFileOverride();
    }

    /**
     * Register the custom route model binding for registry keys.
     *
     * @return void
     */
    protected function registerRouteModelBinding(): void
    {
        $router = $this->app->make(Router::class);

        $router->bind('key', new RegistryKeyBinding());
    }

    /**
     * Overwrite config files with registry data for the current environment.
     *
     * @return void
     */
    protected function applyConfigFileOverride(): void
    {
        if (! $this->app['db']->connection()->getSchemaBuilder()->hasTable('registries')) {
            return;
        }

        $overrides = config('registry.overrides.'.$this->app->environment(), []);

        foreach ($overrides as $configKey => $registryKey) {
            $value = Registry::get($registryKey, null, false, true);
            if ($value !== null) {
                config([$configKey => $value]);
            }
        }
    }
}
