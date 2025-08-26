<?php

namespace JustusTheis\Registry\Tests\Feature;

use Illuminate\Support\Facades\Gate;
use JustusTheis\Registry\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registry_routes_are_protected_when_authorization_enabled()
    {
        config(['registry.authorization.enabled' => true]);
        
        Gate::define('access-registry', fn($user = null) => false);
        
        $response = $this->get(route('registry.index'));
        
        $response->assertStatus(403);
    }

    public function test_registry_routes_allow_access_when_gate_passes()
    {
        config(['registry.authorization.enabled' => true]);
        
        Gate::define('access-registry', fn($user = null) => true);
        
        $response = $this->get(route('registry.index'));
        
        $response->assertStatus(200);
    }

    public function test_registry_routes_allow_access_when_authorization_disabled()
    {
        config(['registry.authorization.enabled' => false]);
        
        Gate::define('access-registry', fn($user = null) => false);
        
        $response = $this->get(route('registry.index'));
        
        $response->assertStatus(200);
    }

    public function test_registry_routes_allow_access_in_bypass_environments()
    {
        config([
            'registry.authorization.enabled' => true,
            'registry.bypass_authorization_envs' => ['testing']
        ]);
        
        Gate::define('access-registry', fn($user = null) => false);
        
        $response = $this->get(route('registry.index'));
        
        $response->assertStatus(200);
    }

    public function test_registry_routes_allow_access_when_gate_not_defined()
    {
        config(['registry.authorization.enabled' => true]);
        
        // Don't define the gate
        
        $response = $this->get(route('registry.index'));
        
        $response->assertStatus(200);
    }

    public function test_custom_gate_name_is_respected()
    {
        config([
            'registry.authorization.enabled' => true,
            'registry.authorization.gate' => 'custom-registry-gate'
        ]);
        
        Gate::define('custom-registry-gate', fn($user = null) => false);
        
        $response = $this->get(route('registry.index'));
        
        $response->assertStatus(403);
    }
}