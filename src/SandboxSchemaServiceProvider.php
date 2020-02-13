<?php

namespace Jecovier\SandboxSchema;

use Jecovier\ResponseMacros\Exceptions\ApiResponseHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
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

        include __DIR__ . '/routes.php';
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