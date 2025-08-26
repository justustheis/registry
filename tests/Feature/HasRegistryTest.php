<?php

namespace JustusTheis\Registry\Tests\Feature;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use JustusTheis\Registry\Tests\TestCase;
use JustusTheis\Registry\Facades\Registry;
use JustusTheis\Registry\Tests\Stubs\TestUser;
use JustusTheis\Registry\Tests\Stubs\TestUserFactory;
use JustusTheis\Registry\Tests\Stubs\TestModelWithRegistry;
use JustusTheis\Registry\Tests\Stubs\TestModelWithoutRegistry;
use JustusTheis\Registry\Exceptions\RegistryValidationException;

class HasRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test table for our stub model
        $this->app['db']->connection()->getSchemaBuilder()->create('test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamp('deleted_at')->nullable();
        });
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
        });
    }

    #[Test]
    public function it_provides_registry_method_on_models_using_trait()
    {
        $model = new TestModelWithRegistry();

        $this->assertInstanceOf(\JustusTheis\Registry\Registry::class, $model->registry());
    }

    #[Test]
    public function registry_method_returns_scoped_instance()
    {
        $model = TestModelWithRegistry::create(['name' => 'Test Model']);
        $registry = $model->registry();

        // Test that the registry is scoped to this model
        $registry->set('test.key', 'test_value');

        // Verify the value is stored with the correct scope
        $this->assertDatabaseHas('registries', [
            'key'              => 'test.key',
            'value'            => 'test_value',
            'registrable_type' => TestModelWithRegistry::class,
            'registrable_id'   => $model->id,
        ]);
    }

    #[Test]
    public function registry_instances_are_isolated_between_different_models()
    {
        $model1 = TestModelWithRegistry::create(['name' => 'Model 1']);
        $model2 = TestModelWithRegistry::create(['name' => 'Model 2']);

        $model1->registry()->set('shared.key', 'value1');
        $model2->registry()->set('shared.key', 'value2');

        $this->assertEquals('value1', $model1->registry()->get('shared.key'));
        $this->assertEquals('value2', $model2->registry()->get('shared.key'));

        $this->assertDatabaseHas('registries', [
            'key'              => 'shared.key',
            'value'            => 'value1',
            'registrable_type' => TestModelWithRegistry::class,
            'registrable_id'   => $model1->id,
        ]);
        $this->assertDatabaseHas('registries', [
            'key'              => 'shared.key',
            'value'            => 'value2',
            'registrable_type' => TestModelWithRegistry::class,
            'registrable_id'   => $model2->id,
        ]);
    }

    #[Test]
    public function it_does_not_cascade_delete_when_configuration_is_disabled()
    {
        Config::set('registry.cascade_on_delete', false);

        $model = TestModelWithRegistry::create(['name' => 'Test Model']);
        $model->registry()->set('test.key', 'test_value');

        $model->delete();

        // Registry entry should still exist since cascade is disabled
        $this->assertDatabaseHas('registries', [
            'key'              => 'test.key',
            'registrable_type' => TestModelWithRegistry::class,
            'registrable_id'   => $model->id,
        ]);
    }

    #[Test]
    public function it_cascades_delete_when_configuration_is_enabled()
    {
        Config::set('registry.cascade_on_delete', true);

        $model = TestUserFactory::create();
        $model->registry()->set('test.key', 'test_value');

        $model->delete();

        $this->assertDatabaseMissing('registries', [
            'key'              => 'test.key',
            'registrable_type' => TestUser::class,
            'registrable_id'   => $model->id,
        ]);
    }

    #[Test]
    public function it_does_not_cascade_delete_if_soft_deleted_and_configuration_is_false()
    {
        Config::set('registry.cascade_on_delete', true);
        Config::set('registry.cascade_on_soft_delete', false);

        $model = TestModelWithRegistry::create(['name' => 'Test Model']);
        $model->registry()->set('test.key', 'test_value');

        $model->delete();

        // Registry entry should still exist since cascade_on_soft_delete is disabled
        $this->assertDatabaseHas('registries', [
            'key'              => 'test.key',
            'registrable_type' => TestModelWithRegistry::class,
            'registrable_id'   => $model->id,
        ]);
    }

    #[Test]
    public function it_cascades_delete_if_soft_deleted_and_configuration_is_true()
    {
        Config::set('registry.cascade_on_delete', true);
        Config::set('registry.cascade_on_soft_delete', true);

        $model = TestModelWithRegistry::create(['name' => 'Test Model']);
        $model->registry()->set('test.key', 'test_value');

        $model->delete();

        // Registry entry should still exist since cascade_on_soft_delete is disabled
        $this->assertDatabaseMissing('registries', [
            'key'              => 'test.key',
            'registrable_type' => TestModelWithRegistry::class,
            'registrable_id'   => $model->id,
        ]);
    }

    #[Test]
    public function it_throws_exception_when_scoping_to_models_without_HasRegistry_trait()
    {
        // Create a table for the model without the trait
        $this->app['db']->connection()->getSchemaBuilder()->create('test_models_without_registry', function ($table) {
            $table->id();
            $table->string('name');
        });

        $model = TestModelWithoutRegistry::create(['name' => 'Test Model Without Trait']);

        $this->expectException(RegistryValidationException::class);
        $this->expectExceptionMessage("Model 'JustusTheis\Registry\Tests\Stubs\TestModelWithoutRegistry' must use the HasRegistry trait to be used with the registry.");

        Registry::for($model);
    }

    #[Test]
    public function it_validates_trait_usage_when_using_registry_method_directly()
    {
        $model = TestModelWithRegistry::create(['name' => 'Test Model']);

        // Direct usage through the trait method should work
        $registry = $model->registry();

        $this->assertInstanceOf(\JustusTheis\Registry\Registry::class, $registry);

        // Should be able to use the registry
        $registry->set('direct.key', 'direct_value');
        $this->assertEquals('direct_value', $registry->get('direct.key'));
    }
}
