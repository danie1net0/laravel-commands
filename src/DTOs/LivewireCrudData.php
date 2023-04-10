<?php

namespace Ddr\LaravelCommands\DTOs;

class LivewireCrudData
{
    public function __construct(
        public string $componentName = '',
        public string $permissionPrefix = '',
        public string $routePrefix = '',
        public string $resourceName = '',
        public string $pluralResourceName = '',
        public string $modelNamespace = '',
        public string $modelName = '',
        public array $directories = [],
        public bool $createTest = true,
    ) {
    }
}
