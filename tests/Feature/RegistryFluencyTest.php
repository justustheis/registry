<?php

namespace JustusTheis\Registry\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use JustusTheis\Registry\Tests\TestCase;
use JustusTheis\Registry\Facades\Registry;
use JustusTheis\Registry\Tests\Stubs\TestUser;
use JustusTheis\Registry\Tests\Stubs\TestUserFactory;

class RegistryFluencyTest extends TestCase
{
    #[Test]
    public function it_can_chain_methods_in_different_orders()
    {
        $user = TestUserFactory::create();

        // Test different method chaining orders
        $result1 = Registry::for($user)->key('chain1')->value('value1')->set();
        $result2 = Registry::key('chain2')->for($user)->value('value2')->set();
        $result3 = Registry::value('value3')->key('chain3')->for($user)->set();

        $this->assertEquals('value1', $result1);
        $this->assertEquals('value2', $result2);
        $this->assertEquals('value3', $result3);

        // All should be scoped to the user
        $this->assertDatabaseHas('registries', [
            'key'              => 'chain1',
            'registrable_type' => TestUser::class,
            'registrable_id'   => 1,
        ]);

        $this->assertDatabaseHas('registries', [
            'key'              => 'chain2',
            'registrable_type' => TestUser::class,
            'registrable_id'   => 1,
        ]);

        $this->assertDatabaseHas('registries', [
            'key'              => 'chain3',
            'registrable_type' => TestUser::class,
            'registrable_id'   => 1,
        ]);
    }

    #[Test]
    public function it_preserves_existing_state_when_scoping_with_for_method()
    {
        $user = TestUserFactory::create();

        // Test that Registry::key('test')->for($user) works due to clone usage
        $result = Registry::key('test.key')->value('test_value')->for($user)->set();

        $this->assertEquals('test_value', $result);

        $this->assertDatabaseHas('registries', [
            'key'              => 'test.key',
            'value'            => 'test_value',
            'registrable_type' => TestUser::class,
            'registrable_id'   => 1,
        ]);
    }

    #[Test]
    public function it_can_chain_global_methods_fluently()
    {
        // Test global method chaining
        $result1 = Registry::key('global1')->value('value1')->set();
        $result2 = Registry::value('value2')->key('global2')->set();
        $result3 = Registry::key('global3')->default('default3')->get();

        $this->assertEquals('value1', $result1);
        $this->assertEquals('value2', $result2);
        $this->assertEquals('default3', $result3);

        // Verify database entries
        $this->assertDatabaseHas('registries', [
            'key'              => 'global1',
            'value'            => 'value1',
            'registrable_type' => null,
            'registrable_id'   => null,
        ]);

        $this->assertDatabaseHas('registries', [
            'key'              => 'global2',
            'value'            => 'value2',
            'registrable_type' => null,
            'registrable_id'   => null,
        ]);
    }

    #[Test]
    public function it_can_mix_scoped_and_unscoped_chaining()
    {
        $user1 = TestUserFactory::create(1, 'User One');
        $user2 = TestUserFactory::create(2, 'User Two');

        // Test mixing scoped and unscoped operations
        Registry::key('mixed.key')->value('global_value')->set();

        $scopedResult1 = Registry::key('mixed.key')->for($user1)->value('user1_value')->set();
        $scopedResult2 = Registry::for($user2)->key('mixed.key')->value('user2_value')->set();

        $globalResult = Registry::get('mixed.key');

        $this->assertEquals('user1_value', $scopedResult1);
        $this->assertEquals('user2_value', $scopedResult2);
        $this->assertEquals('global_value', $globalResult);

        // Verify all entries exist separately
        $this->assertDatabaseHas('registries', [
            'key'              => 'mixed.key',
            'value'            => 'global_value',
            'registrable_type' => null,
        ]);

        $this->assertDatabaseHas('registries', [
            'key'              => 'mixed.key',
            'value'            => 'user1_value',
            'registrable_type' => TestUser::class,
            'registrable_id'   => 1,
        ]);

        $this->assertDatabaseHas('registries', [
            'key'              => 'mixed.key',
            'value'            => 'user2_value',
            'registrable_type' => TestUser::class,
            'registrable_id'   => 2,
        ]);
    }

    #[Test]
    public function it_returns_self_for_fluent_chaining()
    {
        $user = TestUserFactory::create();

        // Test that all fluent methods return the instance for chaining
        $registry1 = Registry::key('fluent1');
        $registry2 = $registry1->value('fluent_value');
        $registry3 = $registry2->default('fluent_default');
        $registry4 = $registry3->for($user);

        // All should be Registry instances (though different instances due to for() cloning)
        $this->assertInstanceOf(\JustusTheis\Registry\Registry::class, $registry1);
        $this->assertInstanceOf(\JustusTheis\Registry\Registry::class, $registry2);
        $this->assertInstanceOf(\JustusTheis\Registry\Registry::class, $registry3);
        $this->assertInstanceOf(\JustusTheis\Registry\Registry::class, $registry4);

        // The first three should be the same instance (key, value, default don't clone)
        $this->assertSame($registry1, $registry2);
        $this->assertSame($registry2, $registry3);

        // The for() method should return a different instance (due to cloning)
        $this->assertNotSame($registry3, $registry4);
    }
}
