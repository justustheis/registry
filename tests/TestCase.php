<?php

namespace JustusTheis\Registry\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use JustusTheis\Registry\RegistryServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return array<int, string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            RegistryServiceProvider::class,
        ];
    }
}
