<?php

use Ddr\LaravelCommands\Console\Commands\Make\LivewireCrud\MakeListCommand;
use Ddr\LaravelCommands\Tests\Models\User;
use Illuminate\Support\Facades\{Config, File};
use Symfony\Component\Console\Exception\RuntimeException;

beforeEach(function (): void {
    Config::set('livewire.class_namespace', 'App\\Http\\Livewire');
    Config::set('livewire.view_path', resource_path('views/livewire'));

    File::deleteDirectory(getPath('Http/Livewire'));
    File::deleteDirectory(getPath('resources/views/livewire', true));
    File::deleteDirectory(getPath('tests/Feature/Livewire', true));
});

it('should run the command successfully', function (): void {
    $this->artisan(MakeListCommand::class, [
        'name' => 'Users/ListUsers',
    ])->assertSuccessful();
});

it('should create a list component with test', function (): void {
    $this->artisan(MakeListCommand::class, [
        'name' => 'Users/ListUsers',
        '--model' => User::class,
    ])->assertSuccessful();

    $componentPath = getPath("Http/Livewire/Users/ListUsers.php");
    $viewPath = getPath("resources/views/livewire/users/list-users.blade.php", true);
    $testPath = getPath("tests/Feature/Livewire/Users/ListUsersTest.php", true);

    expect($componentPath)->exists()
        ->and($componentPath)->toEqualFile("tests/stubs/livewire-crud/list.class.stub")
        ->and($viewPath)->exists()
        ->and($testPath)->exists()
        ->and($testPath)->toEqualFile("tests/stubs/livewire-crud/list.test.stub");
});

it('should create a list component without test', function (): void {
    $this->artisan(MakeListCommand::class, [
        'name' => 'Users/ListUsers',
        '--model' => User::class,
        '--without-test' => true,
    ])->assertSuccessful();

    $componentPath = getPath('Http/Livewire/Users/ListUsers.php');
    $viewPath = getPath('resources/views/livewire/users/list-users.blade.php', true);
    $testPath = getPath('tests/Feature/Livewire/Users/ListUsersTest.php', true);

    expect($componentPath)->exists()
        ->and($componentPath)->toEqualFile('tests/stubs/livewire-crud/list.class.stub')
        ->and($viewPath)->exists()
        ->and($testPath)->not->exists();
});

it('should create a list component with authorize', function (): void {
    $this->artisan(MakeListCommand::class, [
        'name' => 'Users/ListUsers',
        '--model' => User::class,
        '--authorize' => true,
    ])->assertSuccessful();

    $componentPath = getPath('Http/Livewire/Users/ListUsers.php');
    $viewPath = getPath('resources/views/livewire/users/list-users.blade.php', true);
    $testPath = getPath('tests/Feature/Livewire/Users/ListUsersTest.php', true);

    expect($componentPath)->exists()
        ->and($componentPath)->toEqualFile('tests/stubs/livewire-crud/list-with-authorize.class.stub')
        ->and($viewPath)->exists()
        ->and($testPath)->exists()
        ->and($testPath)->toEqualFile('tests/stubs/livewire-crud/list-with-authorize.test.stub');
});

it('should create a list component with data', function (): void {
    $this->artisan(MakeListCommand::class, [
        'name' => 'Users/ListUsers',
        '--model' => User::class,
        '--data' => true,
    ])->assertSuccessful();

    $componentPath = getPath('Http/Livewire/Users/ListUsers.php');
    $viewPath = getPath('resources/views/livewire/users/list-users.blade.php', true);
    $testPath = getPath('tests/Feature/Livewire/Users/ListUsersTest.php', true);

    expect($componentPath)->exists()
        ->and($componentPath)->toEqualFile('tests/stubs/livewire-crud/list-with-data.class.stub')
        ->and($viewPath)->exists()
        ->and($testPath)->exists()
        ->and($testPath)->toEqualFile('tests/stubs/livewire-crud/list-with-data.test.stub');
});

it('should create a list component with authorize and data', function (): void {
    $this->artisan(MakeListCommand::class, [
        'name' => 'Users/ListUsers',
        '--model' => User::class,
        '--authorize' => true,
        '--data' => true,
    ])->assertSuccessful();

    $componentPath = getPath('Http/Livewire/Users/ListUsers.php');
    $viewPath = getPath('resources/views/livewire/users/list-users.blade.php', true);
    $testPath = getPath('tests/Feature/Livewire/Users/ListUsersTest.php', true);

    expect($componentPath)->exists()
        ->and($componentPath)->toEqualFile('tests/stubs/livewire-crud/list-with-authorize-and-data.class.stub')
        ->and($viewPath)->exists()
        ->and($testPath)->exists()
        ->and($testPath)->toEqualFile('tests/stubs/livewire-crud/list-with-authorize-and-data.test.stub');
});

it('should show error when list component has already been created', function (): void {
    $this->artisan(MakeListCommand::class, [
        'name' => 'Users/ListUsers',
    ]);

    $this->artisan(MakeListCommand::class, [
        'name' => 'Users/ListUsers',
    ])->assertExitCode(0)
        ->expectsOutput('Class already exists: app/Http/Livewire/Users/ListUsers.php')
        ->expectsOutput('View already exists: resources/views/livewire/users/list-users.blade.php')
        ->expectsOutput('Test class already exists: tests/Feature/Livewire/Users/ListUsersTest.php');
});

it('should show error when name not informed')
    ->defer(fn () => $this->artisan(MakeListCommand::class))
    ->throws(RuntimeException::class)
    ->expectExceptionMessage('Not enough arguments (missing: "name").');

it('should show error if trying to create a list component with data without passing model', function (): void {
    $this->artisan(MakeListCommand::class, [
        'name' => 'Users/ListUsers',
        '--data' => true,
    ])->assertFailed()
        ->expectsOutputToContain(__('validation.required', ['attribute' => 'model']));
});

it('should show error when model does not exists', function (): void {
    $this->artisan(MakeListCommand::class, [
        'name' => 'Users/ListUsers',
        '--model' => 'FooBar',
    ])->assertExitCode(0)
        ->expectsOutput("Could not find 'FooBar' class.");
});
