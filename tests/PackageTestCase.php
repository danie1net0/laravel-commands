<?php

namespace Ddr\LaravelCommands\Tests;

use Ddr\LaravelCommands\Providers\LaravelCommandsServiceProvider;
use Orchestra\Testbench\TestCase;

class PackageTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelCommandsServiceProvider::class,
        ];
    }
}
