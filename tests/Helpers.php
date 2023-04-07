<?php

use Illuminate\Support\Facades\File;

function getPath(string $path, bool $useBasePath = false): string
{
    if ($useBasePath) {
        return app()->basePath($path);
    }

    return app_path($path);
}

function getStub(string $path): string
{
    return File::get("testes/stubs/{$path}");
}

function getFile(string $path, bool $useBasePath = false): string
{
    if ($useBasePath) {
        $path = getPath($path, true);
    }

    return File::get($path);
}
