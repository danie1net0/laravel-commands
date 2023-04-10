<?php

namespace Ddr\LaravelCommands\Actions;

use Illuminate\Support\Facades\File;

class AnticipateClasses
{
    public function execute(string $classes): array
    {
        $modelsFolder = app_path($classes);

        $files = collect(File::allFiles($modelsFolder))->map(fn ($file) => $file->getFilenameWithoutExtension());

        $files->map(fn ($file) => $files->push("App\\{$classes}\\" . $file));

        return $files->toArray();
    }
}
