<?php

namespace JustusTheis\Registry\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use JustusTheis\Registry\Tests\TestCase;
use JustusTheis\Registry\Tests\Stubs\TestUserFactory;
use JustusTheis\Registry\Tests\Stubs\TestRegistryFactory;

class RegistryRenameTest extends TestCase
{
    #[Test]
    public function it_renames_a_global_key()
    {
        TestRegistryFactory::createGlobal('some.key', 'some value');
        registry()->key('some.key')->rename('some.otherKey');

        $this->assertDatabaseMissing('registries', [
            'key' => 'some.key',
        ]);
        $this->assertDatabaseHas('registries', [
            'key'   => 'some.otherKey',
            'value' => 'some value',
        ]);
    }

    #[Test]
    public function it_renames_a_scoped_key()
    {
        $user = TestUserFactory::create();
        TestRegistryFactory::createScoped('some.key', 'some value', $user);
        registry()->key('some.key')->for($user)->rename('some.otherKey');

        $this->assertDatabaseMissing('registries', [
            'key' => 'some.key',
        ]);
        $this->assertDatabaseHas('registries', [
            'key'              => 'some.otherKey',
            'value'            => 'some value',
            'registrable_type' => get_class($user),
            'registrable_id'   => $user->getKey(),
        ]);
    }

    #[Test]
    public function it_renames_a_global_key_with_its_global_children_from_middle_out()
    {
        TestRegistryFactory::createGlobal('some.parent', 'parent value');
        TestRegistryFactory::createGlobal('some.parent.child', 'child value');
        registry()->key('some.parent')->rename('some.parent2', true);

        // Parent
        $this->assertDatabaseMissing('registries', [
            'key' => 'some.parent',
        ]);
        $this->assertDatabaseHas('registries', [
            'key'   => 'some.parent2',
            'value' => 'parent value',
        ]);

        // Child
        $this->assertDatabaseMissing('registries', [
            'key' => 'some.parent.child',
        ]);
        $this->assertDatabaseHas('registries', [
            'key'   => 'some.parent2.child',
            'value' => 'child value',
        ]);
    }

    #[Test]
    public function it_renames_a_global_key_with_its_global_children_from_beginning()
    {
        TestRegistryFactory::createGlobal('some', 'base value');
        TestRegistryFactory::createGlobal('some.parent', 'parent value');
        TestRegistryFactory::createGlobal('some.parent.child', 'child value');
        registry()->key('some')->rename('other', true);

        // Parent
        $this->assertDatabaseMissing('registries', [
            'key' => 'some.parent',
        ]);
        $this->assertDatabaseHas('registries', [
            'key'   => 'other.parent',
            'value' => 'parent value',
        ]);

        // Child
        $this->assertDatabaseMissing('registries', [
            'key' => 'some.parent.child',
        ]);
        $this->assertDatabaseHas('registries', [
            'key'   => 'other.parent.child',
            'value' => 'child value',
        ]);
    }
}
