<?php

namespace Ddr\LaravelCommands\Console\Commands\Traits;

use Ddr\LaravelCommands\DTOs\LivewireCrudData;
use Illuminate\Support\Facades\File;

trait CreateTest
{
    private function createTest(LivewireCrudData $componentData): bool|string
    {
        $testPath = $this->testPath($componentData);

        if (File::exists($testPath)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Test class already exists:</> {$this->relativeTestPath($componentData)}");

            return false;
        }

        $this->ensureDirectoryExists($testPath);

        File::put($testPath, $this->testContents($componentData));

        return $testPath;
    }

    private function testPath(LivewireCrudData $componentData): string
    {
        $testPath = str(base_path('Tests\Feature\Livewire'))
            ->replace('\\', '/')
            ->replaceFirst('T', 't');

        $baseTestPath = rtrim($testPath, DIRECTORY_SEPARATOR) . '/';

        return $baseTestPath . collect()->concat($directories ?? $componentData->directories)
            ->push($this->className($componentData) . 'Test.php')
            ->implode('/');
    }

    private function relativeTestPath(LivewireCrudData $componentData): string
    {
        return str($this->testPath($componentData))->replaceFirst(base_path() . '/', '');
    }
}
