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
}
