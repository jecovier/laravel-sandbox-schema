<?php

namespace Jecovier\SandboxSchema;

use Illuminate\Support\ServiceProvider;

class SandboxSchemaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            //
        }

        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->make('Jecovier\SandboxSchema\SandboxController');
        $this->app->make('Jecovier\SandboxSchema\SchemaController');
    }
}
