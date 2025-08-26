<?php

namespace JustusTheis\Registry\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use JustusTheis\Registry\Tests\TestCase;
use JustusTheis\Registry\Facades\Registry;
use JustusTheis\Registry\Tests\Stubs\TestUser;
use JustusTheis\Registry\Tests\Stubs\TestUserFactory;
use JustusTheis\Registry\Tests\Stubs\TestRegistryFactory;

class RegistryScopeTest extends TestCase
{
    #[Test]
    public function it_can_set_and_get_scoped_registry_values_via_facade_for_method()
    {
        $user = TestUserFactory::create();

        Registry::for($user)->set('pref.color', 'blue');

        $this->assertEquals('blue', Registry::for($user)->get('pref.color'));
        $this->assertDatabaseHas('registries', [
            'key'              => 'pref.color',
            'value'            => 'blue',
            'registrable_type' => TestUser::class,
            'registrable_id'   => 1,
        ]);
    }

    #[Test]
    public function it_returns_default_for_missing_scoped_values()
    {
        $user = TestUserFactory::create();

        $this->assertEquals('orange', Registry::for($user)->get('pref.color', 'orange'));
    }

    #[Test]
    public function it_scopes_values_correctly_between_different_models()
    {
        $user1 = TestUserFactory::create(1, 'First User');
        $user2 = TestUserFactory::create(2, 'Second User');

        Registry::for($user1)->set('pref.language', 'en');
        Registry::for($user2)->set('pref.language', 'fr');

        $this->assertEquals('en', Registry::for($user1)->get('pref.language'));
        $this->assertEquals('fr', Registry::for($user2)->get('pref.language'));
    }

    #[Test]
    public function it_maintains_separation_between_global_and_scoped_entries()
    {
        $user = TestUserFactory::create();

        Registry::set('shared.key', 'global_value');
        Registry::for($user)->set('shared.key', 'scoped_value');

        $this->assertEquals('global_value', Registry::get('shared.key'));
        $this->assertEquals('scoped_value', Registry::for($user)->get('shared.key'));

        // Both should exist in database
        $this->assertDatabaseHas('registries', [
            'key'              => 'shared.key',
            'value'            => 'global_value',
            'registrable_type' => null,
            'registrable_id'   => null,
        ]);

        $this->assertDatabaseHas('registries', [
            'key'              => 'shared.key',
            'value'            => 'scoped_value',
            'registrable_type' => TestUser::class,
            'registrable_id'   => 1,
        ]);
    }

    #[Test]
    public function it_deletes_a_scoped_key()
    {
        $user = TestUserFactory::create();
        TestRegistryFactory::createScoped('some.key', 'some value', $user);
        Registry::for($user)->delete('some.key');

        $this->assertDatabaseMissing('registries', [
            'key'   => 'some.key',
            'value' => 'some value',
        ]);
    }
}
