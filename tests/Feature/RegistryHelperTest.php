<?php

namespace JustusTheis\Registry\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use JustusTheis\Registry\Tests\TestCase;
use JustusTheis\Registry\Models\RegistryEntry;
use JustusTheis\Registry\Tests\Stubs\TestUserFactory;
use JustusTheis\Registry\Tests\Stubs\TestRegistryFactory;

class RegistryHelperTest extends TestCase
{
    #[Test]
    public function it_returns_registry_when_called_with_no_arguments()
    {
        $registry = registry();

        $this->assertInstanceOf(\JustusTheis\Registry\Registry::class, $registry);
    }

    #[Test]
    public function it_can_get_global_values()
    {
        TestRegistryFactory::createGlobal('app.name', 'Helper App');

        $this->assertEquals('Helper App', registry('app.name'));
        $this->assertEquals('Default', registry('missing.key', 'Default'));
    }

    #[Test]
    public function it_can_get_scoped_values_via_for_method()
    {
        $user = TestUserFactory::create();

        TestRegistryFactory::createScoped('pref.size', 'large', $user);

        $this->assertEquals('large', registry()->for($user)->get('pref.size'));
    }

    #[Test]
    public function it_can_get_scoped_values_inline()
    {
        $user = TestUserFactory::create();

        TestRegistryFactory::createScoped('pref.size', 'large', $user);

        $this->assertEquals('large', registry('pref.size', null, $user));
    }

    #[Test]
    public function it_can_set_encrypted_values()
    {
        registry('encrypted.key', 'some encrypted value', null, true);

        $this->assertDatabaseHas('registries', [
            'key'       => 'encrypted.key',
            'encrypted' => true,
        ]);
        $entry = RegistryEntry::withKey('encrypted.key')->global()->first();
        $this->assertNotEquals('some encrypted value', $entry->getRawOriginal('value'));
    }

    #[Test]
    public function it_can_set_type()
    {
        registry('bool.key', 'true', null, false, 'bool');

        $this->assertDatabaseHas('registries', [
            'key'  => 'bool.key',
            'type' => 'bool',
        ]);
    }
}
