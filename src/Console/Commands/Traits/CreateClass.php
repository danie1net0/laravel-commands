<?php

namespace Ddr\LaravelCommands\Console\Commands\Traits;

use Ddr\LaravelCommands\DTOs\LivewireCrudData;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait CreateClass
{
    protected function createClass(LivewireCrudData $componentData): bool|string
    {
        $classPath = $this->classPath($componentData);

        if (File::exists($classPath)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->relativeClassPath($componentData)}");

            return false;
        }

        $this->ensureDirectoryExists($classPath);

        File::put($classPath, $this->classContents($componentData));

        return $classPath;
    }

    protected function classPath(LivewireCrudData $componentData): string
    {
        $name = str(config('livewire.class_namespace'))
            ->finish('\\')
            ->replaceFirst(app()->getNamespace(), '');

        $classPath = app('path') . '/' . str_replace('\\', '/', $name);

        return rtrim($classPath, DIRECTORY_SEPARATOR) . '/' . collect()
            ->concat($componentData->directories)
            ->push($this->className($componentData) . '.php')
            ->implode('/');
    }

    protected function className(LivewireCrudData $componentData): string
    {
        return str($componentData->componentName)->camel()->kebab()->studly();
    }

    protected function relativeClassPath(LivewireCrudData $componentData): string
    {
        return str($this->classPath($componentData))->replaceFirst(base_path() . DIRECTORY_SEPARATOR, '');
    }

    protected function ensureDirectoryExists($path): void
    {
        if (! File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, $recursive = true, $force = true);
        }
    }

    protected function classNamespace(LivewireCrudData $componentData): string
    {
        $baseClassNamespace = config('livewire.class_namespace');
        $directories = array_map([Str::class, 'studly'], $componentData->directories);

        return empty($directories)
            ? $baseClassNamespace
            : $baseClassNamespace . '\\' . collect()
                ->concat($directories)
                ->map([Str::class, 'studly'])
                ->implode('\\');
    }

    protected function viewName(LivewireCrudData $componentData): string
    {
        $viewPath = config('livewire.view_path');
        $baseViewPath = rtrim($viewPath, DIRECTORY_SEPARATOR) . '/';

        return collect()
            ->when($viewPath !== resource_path(), fn ($collection) => $collection->concat(explode('/', str($baseViewPath)->after(resource_path('views')))))
            ->filter()
            ->concat($componentData->directories)
            ->map([Str::class, 'kebab'])
            ->push(str($componentData->componentName)->camel()->kebab())
            ->implode('.');
    }
}
