<?php

namespace Ddr\LaravelCommands\Providers;

use Ddr\LaravelCommands\Console\Commands\Make\ActionMakeCommand;
use Illuminate\Support\ServiceProvider;

class LaravelCommandsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ActionMakeCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../views' => resource_path('views/vendor/laravel-commands'),
        ], 'laravel-commands-stubs');
    }
}
