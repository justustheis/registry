<?php

namespace JustusTheis\Registry\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use JustusTheis\Registry\Tests\TestCase;
use JustusTheis\Registry\Facades\Registry;
use JustusTheis\Registry\Models\RegistryEntry;
use JustusTheis\Registry\Tests\Stubs\TestUserFactory;

class RegistryEncryptionTest extends TestCase
{
    #[Test]
    public function it_encrypts_string_values()
    {
        Registry::set('encrypted.string', 'sensitive_data', true);

        $entry = RegistryEntry::withKey('encrypted.string')->global()->first();

        $this->assertTrue($entry->encrypted);
        $this->assertNotEquals('sensitive_data', $entry->getRawOriginal('value'));
        $this->assertEquals('sensitive_data', Registry::get('encrypted.string'));
    }

    #[Test]
    public function it_encrypts_array_values()
    {
        $sensitiveArray = ['username', 'password'];

        Registry::set('encrypted.array', $sensitiveArray, true);

        $entry = RegistryEntry::withKey('encrypted.array')->global()->first();

        $this->assertTrue($entry->encrypted);
        $this->assertNotEquals(json_encode($sensitiveArray), $entry->getRawOriginal('value'));
        $this->assertEquals($sensitiveArray, Registry::get('encrypted.array'));
    }

    #[Test]
    public function it_encrypts_object_values()
    {
        $sensitiveObject = new \JustusTheis\Registry\Tests\Stubs\SerializableTestObject();

        Registry::set('encrypted.object', $sensitiveObject, true);

        $entry = RegistryEntry::withKey('encrypted.object')->global()->first();

        $this->assertTrue($entry->encrypted);
        $this->assertNotEquals('serializable value', $entry->getRawOriginal('value'));
        $this->assertEquals('serializable value', Registry::get('encrypted.object'));
    }

    #[Test]
    public function it_stores_unencrypted_values_normally()
    {
        Registry::set('plain.value', 'normal_data', false);

        $entry = RegistryEntry::withKey('plain.value')->global()->first();

        $this->assertFalse($entry->encrypted);
        $this->assertEquals('normal_data', $entry->getRawOriginal('value'));
        $this->assertEquals('normal_data', Registry::get('plain.value'));
    }

    #[Test]
    public function it_handles_mixed_encrypted_and_unencrypted_entries()
    {
        Registry::set('encrypted.data', 'secret', true);
        Registry::set('plain.data', 'public', false);

        $this->assertEquals('secret', Registry::get('encrypted.data'));
        $this->assertEquals('public', Registry::get('plain.data'));

        $encryptedEntry = RegistryEntry::withKey('encrypted.data')->global()->first();
        $plainEntry = RegistryEntry::withKey('plain.data')->global()->first();

        $this->assertTrue($encryptedEntry->encrypted);
        $this->assertFalse($plainEntry->encrypted);
        $this->assertNotEquals('secret', $encryptedEntry->getRawOriginal('value'));
        $this->assertEquals('public', $plainEntry->getRawOriginal('value'));
    }

    #[Test]
    public function it_encrypts_scoped_entries()
    {
        $user = TestUserFactory::create();

        Registry::for($user)->set('encrypted.user.setting', 'user_secret', true);

        $entry = RegistryEntry::withKey('encrypted.user.setting')->forModel($user)->first();

        $this->assertTrue($entry->encrypted);
        $this->assertNotEquals('user_secret', $entry->getRawOriginal('value'));
        $this->assertEquals('user_secret', Registry::for($user)->get('encrypted.user.setting'));
    }

    #[Test]
    public function it_handles_encrypted_entries_with_explicit_type_casting()
    {
        Registry::set('encrypted.integer', 42, true, 'integer');
        Registry::set('encrypted.boolean', true, true, 'boolean');
        Registry::set('encrypted.float', 3.14, true, 'float');
        Registry::set('encrypted.array', ['test one', 'test two'], true, 'array');

        $this->assertSame(42, Registry::get('encrypted.integer'));
        $this->assertTrue(Registry::get('encrypted.boolean'));
        $this->assertSame(3.14, Registry::get('encrypted.float'));
        $this->assertIsArray(Registry::get('encrypted.array'));
    }

    #[Test]
    public function it_handles_encrypted_entries_with_automatic_type_casting()
    {
        config(['registry.auto_cast_types' => true]);

        Registry::set('encrypted.integer', '42', true);
        Registry::set('encrypted.boolean', 'true', true);
        Registry::set('encrypted.float', '3.14', true);
        Registry::set('encrypted.array', ['test one', 'test two'], true);

        $this->assertIsInt(Registry::get('encrypted.integer'));
        $this->assertTrue(Registry::get('encrypted.boolean'));
        $this->assertIsFloat(Registry::get('encrypted.float'));
        $this->assertIsArray(Registry::get('encrypted.array'));
    }

    #[Test]
    public function it_updates_encrypted_entries()
    {
        Registry::set('encrypted.update', 'initial_secret', true);
        Registry::set('encrypted.update', 'updated_secret', true);

        $this->assertEquals('updated_secret', Registry::get('encrypted.update'));

        $entry = RegistryEntry::withKey('encrypted.update')->global()->first();
        $this->assertTrue($entry->encrypted);
        $this->assertNotEquals('updated_secret', $entry->getRawOriginal('value'));
    }

    #[Test]
    public function it_can_change_encryption_status_on_update()
    {
        Registry::set('changeable.encryption', 'secret_data', true);

        $this->assertEquals('secret_data', Registry::get('changeable.encryption'));
        $entry = RegistryEntry::withKey('changeable.encryption')->global()->first();
        $this->assertTrue($entry->encrypted);

        Registry::set('changeable.encryption', 'public_data', false);

        $this->assertEquals('public_data', Registry::get('changeable.encryption'));
        $entry->refresh();
        $this->assertFalse($entry->encrypted);
        $this->assertEquals('public_data', $entry->getRawOriginal('value'));
    }

    #[Test]
    public function it_filters_encrypted_entries_with_scope()
    {
        Registry::set('encrypted.one', 'secret1', true);
        Registry::set('plain.one', 'public1', false);
        Registry::set('encrypted.two', 'secret2', true);

        $encryptedEntries = RegistryEntry::encrypted()->get();

        $this->assertCount(2, $encryptedEntries);
        $this->assertTrue($encryptedEntries->every(fn ($entry) => $entry->encrypted));
    }

    #[Test]
    public function it_handles_null_values_with_encryption()
    {
        Registry::set('encrypted.null', null, true);

        $this->assertNull(Registry::get('encrypted.null'));

        $entry = RegistryEntry::withKey('encrypted.null')->global()->first();
        $this->assertTrue($entry->encrypted);
    }

    #[Test]
    public function it_handles_empty_string_with_encryption()
    {
        Registry::set('encrypted.empty', '', true);

        $this->assertEquals('', Registry::get('encrypted.empty'));

        $entry = RegistryEntry::withKey('encrypted.empty')->global()->first();
        $this->assertTrue($entry->encrypted);
        $this->assertNotEquals('', $entry->getRawOriginal('value'));
    }

    #[Test]
    public function it_supports_fluent_encrypt_api()
    {
        Registry::key('fluent.encrypted')->value('sensitive_data')->encrypt()->set();

        $this->assertEquals('sensitive_data', Registry::get('fluent.encrypted'));

        $entry = RegistryEntry::withKey('fluent.encrypted')->global()->first();
        $this->assertTrue($entry->encrypted);
        $this->assertNotEquals('sensitive_data', $entry->getRawOriginal('value'));
    }

    #[Test]
    public function it_explicit_encryption_parameter_overrides_fluent_setting()
    {
        // Fluent setting says encrypt, but explicit parameter says don't
        Registry::key('override.test')->value('data')->encrypt()->set(encrypted: false);

        $entry = RegistryEntry::withKey('override.test')->global()->first();
        $this->assertFalse($entry->encrypted);
        $this->assertEquals('data', $entry->getRawOriginal('value'));
    }
}
