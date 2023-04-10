<?php

namespace Ddr\LaravelCommands\Providers;

use Ddr\LaravelCommands\Console\Commands\Make\ActionMakeCommand;
use Ddr\LaravelCommands\Console\Commands\Make\LivewireCrud\MakeListCommand;
use Illuminate\Support\ServiceProvider;

class LaravelCommandsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../resources/config/laravel-commands.php', 'laravel-commands');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ActionMakeCommand::class,
                MakeListCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/laravel-commands'),
        ], 'laravel-commands-stubs');

        $this->publishes([
            __DIR__ . '/../../resources/config/laravel-commands.php' => config_path('laravel-commands.php'),
        ], 'laravel-commands-config');
    }
}
