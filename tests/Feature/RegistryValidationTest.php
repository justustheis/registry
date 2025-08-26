<?php

namespace JustusTheis\Registry\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use JustusTheis\Registry\Tests\TestCase;
use JustusTheis\Registry\Facades\Registry;
use JustusTheis\Registry\Tests\Stubs\SerializableTestObject;
use JustusTheis\Registry\Exceptions\RegistryValidationException;

class RegistryValidationTest extends TestCase
{
    #[Test]
    public function it_sets_serializable_object_with_toString()
    {
        $serializableObject = new SerializableTestObject();

        Registry::set('object.key', $serializableObject);

        $this->assertDatabaseHas('registries', [
            'key'   => 'object.key',
            'value' => 'serializable value',
        ]);
    }

    #[Test]
    public function it_throws_exception_for_invalid_key_format()
    {
        $this->expectException(RegistryValidationException::class);
        $this->expectExceptionMessage("Invalid key format: 'invalid@key'. Keys must contain only alphanumeric characters, dots, underscores, and hyphens.");

        Registry::set('invalid@key', 'value');
    }

    #[Test]
    public function it_normalizes_backslashes_in_keys()
    {
        Registry::set('section\\subsection\\key', 'value');

        $this->assertDatabaseHas('registries', [
            'key'   => 'section.subsection.key',
            'value' => 'value',
        ]);
    }

    #[Test]
    public function it_normalizes_forward_slashes_in_keys()
    {
        Registry::set('section/subsection/key', 'value');

        $this->assertDatabaseHas('registries', [
            'key'   => 'section.subsection.key',
            'value' => 'value',
        ]);
    }

    #[Test]
    public function it_normalizes_mixed_slashes_in_keys()
    {
        Registry::set('section\\subsection/key', 'value');

        $this->assertDatabaseHas('registries', [
            'key'   => 'section.subsection.key',
            'value' => 'value',
        ]);
    }
}
