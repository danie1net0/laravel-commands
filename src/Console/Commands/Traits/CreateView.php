<?php

namespace Ddr\LaravelCommands\Console\Commands\Traits;

use Ddr\LaravelCommands\DTOs\LivewireCrudData;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait CreateView
{
    protected function createView(LivewireCrudData $componentData): bool|string
    {
        $viewPath = $this->viewPath($componentData);

        if (File::exists($viewPath)) {
            $this->line("<fg=red;options=bold>View already exists:</> {$this->relativeViewPath($componentData)}");

            return false;
        }

        $this->ensureDirectoryExists($viewPath);

        File::put($viewPath, $this->viewContents());

        return $viewPath;
    }

    protected function viewPath(LivewireCrudData $componentData): string
    {
        $baseViewPath = rtrim(config('livewire.view_path'), DIRECTORY_SEPARATOR) . '/';

        return $baseViewPath . collect()
            ->concat($componentData->directories)
            ->map([Str::class, 'kebab'])
            ->push(str($componentData->componentName)->camel()->kebab() . '.blade.php')
            ->implode(DIRECTORY_SEPARATOR);
    }

    protected function relativeViewPath(LivewireCrudData $componentData): string
    {
        return str($this->viewPath($componentData))->replaceFirst(base_path() . '/', '');
    }
}
