<?php

namespace JustusTheis\Registry\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use JustusTheis\Registry\Tests\TestCase;
use JustusTheis\Registry\Facades\Registry;
use JustusTheis\Registry\Tests\Stubs\TestUserFactory;
use JustusTheis\Registry\Tests\Stubs\TestRegistryFactory;

class RegistryCacheTest extends TestCase
{
    #[Test]
    public function it_caches_global_registry_entries_on_first_load()
    {
        // Set up a registry entry in the database
        TestRegistryFactory::createGlobal('some.key', 'some value');

        DB::enableQueryLog();

        // First call should hit the database
        Registry::get('some.key');
        $this->assertNotEmpty(DB::getQueryLog(), 'First get() call should hit the database');

        DB::flushQueryLog();

        // Second call should use cache (no additional database queries)
        Registry::get('some.key');
        $this->assertEmpty(DB::getQueryLog(), 'Second get() call should not hit the database when cached');
    }

    #[Test]
    public function it_caches_scoped_registry_entries_on_first_load()
    {
        $user = TestUserFactory::create();
        TestRegistryFactory::createScoped('user.pref', 'scoped value', $user);

        DB::enableQueryLog();

        // First call should hit database
        Registry::for($user)->get('user.pref');
        $this->assertNotEmpty(DB::getQueryLog(), 'First get() call should hit the database');

        DB::flushQueryLog();

        // Second call should use cache
        Registry::for($user)->get('user.pref');
        $this->assertEmpty(DB::getQueryLog(), 'Second get() call should not hit the database when cached');
    }

    #[Test]
    public function it_invalidates_global_cache_when_value_is_updated()
    {
        // Set initial value
        Registry::set('cache.test', 'initial value');

        // Load into cache
        $initial = Registry::get('cache.test');

        // Update the value (should invalidate cache)
        Registry::set('cache.test', 'updated value');

        // Get key again
        $updated = Registry::get('cache.test');

        $this->assertEquals('initial value', $initial);
        $this->assertEquals('updated value', $updated);
    }

    #[Test]
    public function it_invalidates_scoped_cache_when_value_is_updated()
    {
        $user = TestUserFactory::create();

        // Set and load initial scoped value
        Registry::for($user)->set('user.setting', 'initial setting');
        $initial = Registry::for($user)->get('user.setting');

        // Update the scoped value
        Registry::for($user)->set('user.setting', 'updated setting');

        // Get key again
        $updated = Registry::for($user)->get('user.setting');

        $this->assertEquals('initial setting', $initial);
        $this->assertEquals('updated setting', $updated);
    }

    #[Test]
    public function it_maintains_separate_cache_for_global_and_scoped_entries()
    {
        $user = TestUserFactory::create();

        // Set same key for both global and scoped
        Registry::set('shared.setting', 'global value');
        Registry::for($user)->set('shared.setting', 'scoped value');

        // Load both into cache
        $globalValue = Registry::get('shared.setting');
        $scopedValue = Registry::for($user)->get('shared.setting');

        DB::enableQueryLog();
        DB::flushQueryLog();

        // Both should be served from cache
        $cachedGlobal = Registry::get('shared.setting');
        $cachedScoped = Registry::for($user)->get('shared.setting');
        $this->assertEmpty(DB::getQueryLog(), 'Both cached values should be served without database queries');

        $this->assertEquals('global value', $globalValue);
        $this->assertEquals('scoped value', $scopedValue);
        $this->assertEquals('global value', $cachedGlobal);
        $this->assertEquals('scoped value', $cachedScoped);
    }

    #[Test]
    public function it_maintains_separate_cache_for_different_scoped_models()
    {
        $user1 = TestUserFactory::create(1, 'User One');
        $user2 = TestUserFactory::create(2, 'User Two');

        // Set same key for different users
        Registry::for($user1)->set('user.name', 'First User Setting');
        Registry::for($user2)->set('user.name', 'Second User Setting');

        // Load into cache
        $value1 = Registry::for($user1)->get('user.name');
        $value2 = Registry::for($user2)->get('user.name');

        DB::enableQueryLog();
        DB::flushQueryLog();

        // Should serve from separate caches
        $cached1 = Registry::for($user1)->get('user.name');
        $cached2 = Registry::for($user2)->get('user.name');
        $this->assertEmpty(DB::getQueryLog(), 'Both cached values should be served without database queries');

        $this->assertEquals('First User Setting', $value1);
        $this->assertEquals('Second User Setting', $value2);
        $this->assertEquals('First User Setting', $cached1);
        $this->assertEquals('Second User Setting', $cached2);
    }

    #[Test]
    public function it_only_invalidates_cache_for_specific_key_and_scope()
    {
        $user = TestUserFactory::create();

        // Set multiple keys
        Registry::set('global.key1', 'global value 1');
        Registry::set('global.key2', 'global value 2');
        Registry::for($user)->set('scoped.key1', 'scoped value 1');
        Registry::for($user)->set('scoped.key2', 'scoped value 2');

        // Load all into cache
        Registry::get('global.key1');
        Registry::get('global.key2');
        Registry::for($user)->get('scoped.key1');
        Registry::for($user)->get('scoped.key2');

        // Update only one key - this should invalidate only that specific cache entry
        Registry::set('global.key1', 'updated global value 1');

        // Now test that only the updated key hits the database, others use cache
        DB::enableQueryLog();
        DB::flushQueryLog();

        // The updated key should return the new value (already updated via set above)
        $updatedValue = Registry::get('global.key1');

        // These should be served from cache (no additional DB queries)
        $cachedGlobal = Registry::get('global.key2');
        $cachedScoped1 = Registry::for($user)->get('scoped.key1');
        $cachedScoped2 = Registry::for($user)->get('scoped.key2');

        // Verify values are correct
        $this->assertEquals('updated global value 1', $updatedValue);
        $this->assertEquals('global value 2', $cachedGlobal);
        $this->assertEquals('scoped value 1', $cachedScoped1);
        $this->assertEquals('scoped value 2', $cachedScoped2);

        // Verify that non-updated keys didn't hit the database
        $this->assertEmpty(DB::getQueryLog(), 'Keys should have been served from cache.');
    }

    #[Test]
    public function it_caches_default_values_when_key_does_not_exist()
    {
        // Get non-existent key with default (this should set and cache the default)
        Registry::get('nonexistent.key', 'default value');

        DB::flushQueryLog();
        DB::enableQueryLog();

        // Second call should use cache
        Registry::get('nonexistent.key', 'different default');

        $this->assertEmpty(DB::getQueryLog(), 'Cached default value should not hit database');
    }
}
