<?php

namespace JustusTheis\Registry\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use JustusTheis\Registry\Tests\TestCase;
use JustusTheis\Registry\Facades\Registry;
use JustusTheis\Registry\Tests\Stubs\TestRegistryFactory;

class RegistryGlobalTest extends TestCase
{
    #[Test]
    public function it_gets_the_value_from_a_key()
    {
        TestRegistryFactory::createGlobal('some.key', 'some value');

        $this->assertEquals('some value', Registry::get('some.key'));
    }

    #[Test]
    public function it_sets_a_registry_entry()
    {
        Registry::set('some.key', 'some value');

        $this->assertDatabaseHas('registries', [
            'key'   => 'some.key',
            'value' => 'some value',
        ]);
    }

    #[Test]
    public function it_returns_the_default_when_getting_a_non_existing_key()
    {
        $this->assertEquals(
            'some default value',
            Registry::get('some.key', 'some default value')
        );
    }

    #[Test]
    public function it_returns_null_when_getting_a_non_existing_key_without_default()
    {
        $this->assertNull(
            Registry::get('some.key')
        );
    }

    #[Test]
    public function it_sets_the_entry_to_default_value_when_getting_a_non_existing_key()
    {
        Registry::get('some.key', 'some default');

        $this->assertDatabaseHas('registries', [
            'key'   => 'some.key',
            'value' => 'some default',
        ]);
    }

    #[Test]
    public function it_returns_default_for_missing_global_values()
    {
        $this->assertEquals('default', Registry::get('missing.key', 'default'));
    }

    #[Test]
    public function it_deletes_a_global_key()
    {
        TestRegistryFactory::createGlobal('some.key', 'some value');
        Registry::delete('some.key');

        $this->assertDatabaseMissing('registries', [
            'key'   => 'some.key',
            'value' => 'some value',
        ]);
    }

    #[Test]
    public function it_handles_non_existing_keys_while_deleting()
    {
        $value = Registry::delete('some.key');
        $this->assertFalse($value);
    }
}
