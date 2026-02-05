<?php

namespace JustusTheis\Registry\Tests\Feature;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use JustusTheis\Registry\Tests\TestCase;
use JustusTheis\Registry\Models\RegistryEntry;
use JustusTheis\Registry\Facades\Registry;
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

    #[Test]
    public function it_can_filter_global_values_by_pattern()
    {
        TestRegistryFactory::createGlobal('radius.mayByPass.1', '192.168.1.1-192.168.1.255');
        TestRegistryFactory::createGlobal('radius.mayByPass.2', '10.0.0.1-10.0.0.255');
        TestRegistryFactory::createGlobal('radius.other.key', 'should not match');

        $results = registry_filter('radius.mayByPass.%');

        $this->assertCount(2, $results);
        $this->assertEquals('192.168.1.1-192.168.1.255', $results['radius.mayByPass.1']);
        $this->assertEquals('10.0.0.1-10.0.0.255', $results['radius.mayByPass.2']);
    }

    #[Test]
    public function it_can_filter_scoped_values_by_pattern()
    {
        $user = TestUserFactory::create();

        TestRegistryFactory::createScoped('settings.theme.color', 'blue', $user);
        TestRegistryFactory::createScoped('settings.theme.font', 'arial', $user);
        TestRegistryFactory::createScoped('settings.other', 'ignored', $user);

        $results = registry_filter('settings.theme.%', $user);

        $this->assertCount(2, $results);
        $this->assertEquals('blue', $results['settings.theme.color']);
        $this->assertEquals('arial', $results['settings.theme.font']);
    }

    #[Test]
    public function it_returns_empty_collection_when_no_matches()
    {
        $results = registry_filter('nonexistent.%');

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertCount(0, $results);
    }

    #[Test]
    public function it_can_filter_with_facade()
    {
        TestRegistryFactory::createGlobal('app.config.debug', 'true');
        TestRegistryFactory::createGlobal('app.config.env', 'testing');

        $results = Registry::filter('app.config.%');

        $this->assertCount(2, $results);
    }
}
