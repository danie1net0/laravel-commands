<?php

namespace Ddr\LaravelCommands\Console\Commands\Traits;

use Ddr\LaravelCommands\DTOs\LivewireCrudData;
use Illuminate\Support\Arr;

trait InputData
{
    protected function inputName(LivewireCrudData $livewireCrudData): self
    {
        $componentName = str_replace(['.', '\\'], '/', $this->argument('name'));

        $livewireCrudData->directories = preg_split('/[.\/(\\\\)]+/', $componentName);

        $livewireCrudData->componentName = array_pop($livewireCrudData->directories);

        return $this;
    }

    protected function inputModel(LivewireCrudData $livewireCrudData): self
    {
        $modelNamespace = $this->option('model');

        if (! $modelNamespace) {
            return $this;
        }

        $modelExists = class_exists($modelNamespace);

        if (! $modelExists) {
            $this->error("Could not find '{$modelNamespace}' class.");
        }

        $livewireCrudData->modelNamespace = $modelNamespace;

        $livewireCrudData->modelName = Arr::last(explode('\\', $modelNamespace));

        $livewireCrudData->resourceName = str($livewireCrudData->modelName)->camel();
        $livewireCrudData->pluralResourceName = str($livewireCrudData->resourceName)->plural();

        return $this;
    }

    protected function inputCreateTest(LivewireCrudData $livewireCrudData): self
    {
        $livewireCrudData->createTest = ! $this->option('without-test');

        return $this;
    }
}
