<?php

namespace JustusTheis\Registry;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
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

        // Configure Inertia to use the package's app template for registry routes
        if (class_exists(\Inertia\Inertia::class)) {
            \Inertia\Inertia::setRootView('registry::app');
        }
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
}
