<?php

namespace JustusTheis\Registry\Tests\Unit;

use Mockery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use JustusTheis\Registry\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;
use JustusTheis\Registry\Http\Middleware\RegistryAuthorizationMiddleware;

class RegistryAuthorizationMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_middleware_allows_access_when_authorization_disabled()
    {
        config(['registry.authorization.enabled' => false]);
        
        $middleware = new RegistryAuthorizationMiddleware();
        $request = Request::create('/');
        $next = fn($req) => new Response('OK');
        
        $response = $middleware->handle($request, $next);
        
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_allows_access_in_bypass_environments()
    {
        config([
            'registry.authorization.enabled' => true,
            'registry.bypass_authorization_envs' => ['testing']
        ]);
        
        $middleware = new RegistryAuthorizationMiddleware();
        $request = Request::create('/');
        $next = fn($req) => new Response('OK');
        
        $response = $middleware->handle($request, $next);
        
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_allows_access_when_gate_not_defined()
    {
        config(['registry.authorization.enabled' => true]);
        app()->detectEnvironment(fn() => 'production'); // Not in bypass envs
        
        $middleware = new RegistryAuthorizationMiddleware();
        $request = Request::create('/');
        $next = fn($req) => new Response('OK');
        
        $response = $middleware->handle($request, $next);
        
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_denies_access_when_gate_denies()
    {
        config(['registry.authorization.enabled' => true]);
        app()->detectEnvironment(fn() => 'production'); // Not in bypass envs
        
        Gate::define('access-registry', fn($user = null) => false);
        
        $middleware = new RegistryAuthorizationMiddleware();
        $request = Request::create('/');
        $next = fn($req) => new Response('OK');
        
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Access to registry is not authorized.');
        
        $middleware->handle($request, $next);
    }

    public function test_middleware_allows_access_when_gate_allows()
    {
        config(['registry.authorization.enabled' => true]);
        app()->detectEnvironment(fn() => 'production'); // Not in bypass envs
        
        Gate::define('access-registry', fn($user = null) => true);
        
        $middleware = new RegistryAuthorizationMiddleware();
        $request = Request::create('/');
        $next = fn($req) => new Response('OK');
        
        $response = $middleware->handle($request, $next);
        
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_uses_custom_gate_name()
    {
        config([
            'registry.authorization.enabled' => true,
            'registry.authorization.gate' => 'custom-gate'
        ]);
        app()->detectEnvironment(fn() => 'production'); // Not in bypass envs
        
        Gate::define('custom-gate', fn($user = null) => false);
        
        $middleware = new RegistryAuthorizationMiddleware();
        $request = Request::create('/');
        $next = fn($req) => new Response('OK');
        
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Access to registry is not authorized.');
        
        $middleware->handle($request, $next);
    }
}