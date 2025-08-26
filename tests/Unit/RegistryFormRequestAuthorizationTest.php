<?php

namespace JustusTheis\Registry\Tests\Unit;

use Illuminate\Support\Facades\Gate;
use JustusTheis\Registry\Tests\TestCase;
use JustusTheis\Registry\Http\Requests\Registry\StoreRegistryKeyRequest;

class RegistryFormRequestAuthorizationTest extends TestCase
{
    public function test_form_request_allows_when_authorization_disabled()
    {
        config(['registry.authorization.enabled' => false]);
        
        $request = new StoreRegistryKeyRequest();
        
        $this->assertTrue($request->authorize());
    }

    public function test_form_request_allows_in_bypass_environments()
    {
        config([
            'registry.authorization.enabled' => true,
            'registry.bypass_authorization_envs' => ['testing']
        ]);
        
        $request = new StoreRegistryKeyRequest();
        
        $this->assertTrue($request->authorize());
    }

    public function test_form_request_allows_when_gate_allows()
    {
        config(['registry.authorization.enabled' => true]);
        app()->detectEnvironment(fn() => 'production'); // Not in bypass envs
        
        Gate::define('access-registry', fn($user = null) => true);
        
        $request = new StoreRegistryKeyRequest();
        
        $this->assertTrue($request->authorize());
    }

    public function test_form_request_denies_when_gate_denies()
    {
        config(['registry.authorization.enabled' => true]);
        app()->detectEnvironment(fn() => 'production'); // Not in bypass envs
        
        Gate::define('access-registry', fn($user = null) => false);
        
        $request = new StoreRegistryKeyRequest();
        
        $this->assertFalse($request->authorize());
    }

    public function test_form_request_allows_when_gate_not_defined()
    {
        config(['registry.authorization.enabled' => true]);
        app()->detectEnvironment(fn() => 'production'); // Not in bypass envs
        
        // Don't define any gate
        
        $request = new StoreRegistryKeyRequest();
        
        $this->assertTrue($request->authorize());
    }
}