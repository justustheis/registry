<?php

namespace JustusTheis\Registry\Http\Middleware;

use Inertia\Middleware;
use Illuminate\Http\Request;

class RegistryInertiaMiddleware extends Middleware
{
    /**
     * The root Blade template for registry routes.
     *
     * @var string
     */
    protected $rootView = 'registry::app';

    /**
     * Determine the current asset version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default for registry routes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
        ];
    }
}