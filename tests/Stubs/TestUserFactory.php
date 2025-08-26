<?php

namespace JustusTheis\Registry\Tests\Stubs;

class TestUserFactory
{
    /**
     * Create a test user with the given ID and name.
     *
     * @param  int      $id
     * @param  string   $name
     * @return TestUser
     */
    public static function create(int $id = 1, string $name = 'Test User'): TestUser
    {
        $user = new TestUser();
        $user->id = $id;
        $user->name = $name;
        $user->exists = true;

        return $user;
    }

    /**
     * Create multiple test users.
     *
     * @param  int             $count
     * @return array<TestUser>
     */
    public static function createMultiple(int $count): array
    {
        $users = [];

        for ($i = 1; $i <= $count; $i++) {
            $users[] = static::create($i, "Test User {$i}");
        }

        return $users;
    }
}
