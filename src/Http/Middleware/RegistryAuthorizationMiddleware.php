<?php

namespace JustusTheis\Registry\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class RegistryAuthorizationMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Registry Authorization Middleware
    |--------------------------------------------------------------------------
    |
    | This middleware checks if the current user is authorized to access
    | the registry frontend interface using the configured gate. It respects
    | bypass environments and authorization settings.
    |
    */

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if authorization is disabled
        if (!config('registry.authorization.enabled', true)) {
            return $next($request);
        }

        // Check if we're in a bypass environment
        $bypassEnvs = config('registry.bypass_authorization_envs', []);
        if (in_array(app()->environment(), $bypassEnvs)) {
            return $next($request);
        }

        // Get the configured gate name
        $gate = config('registry.authorization.gate', 'access-registry');

        // Check if the gate exists
        if (Gate::has($gate)) {
            // If gate exists, check authorization
            if (Gate::denies($gate)) {
                abort(403, 'Access to registry is not authorized.');
            }
        }
        // If gate doesn't exist, allow access (graceful degradation)

        return $next($request);
    }
}