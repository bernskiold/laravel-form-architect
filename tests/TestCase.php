<?php

namespace BernskioldMedia\LaravelFormArchitect\Tests;

use BernskioldMedia\LaravelFormArchitect\FormArchitectServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['view']->addLocation(__DIR__.'/views');

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'BernskioldMedia\\LaravelFormArchitect\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            FormArchitectServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}
