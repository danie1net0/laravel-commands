<?php

use Ddr\LaravelCommands\Tests\PackageTestCase;
use Illuminate\Support\Facades\File;

uses(PackageTestCase::class)->in(__DIR__);

expect()->extend('exists', function () {
    expect(File::exists($this->value))->toBeTrue();

    return $this;
});

expect()->extend('toEqualFile', function (string $path) {
    expect(getFile($this->value))->toEqual(getFile($path));

    return $this;
});
