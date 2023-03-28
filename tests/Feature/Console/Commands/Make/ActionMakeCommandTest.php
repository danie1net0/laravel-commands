<?php

use Ddr\LaravelCommands\Console\Commands\Make\ActionMakeCommand;
use Ddr\LaravelCommands\Tests\Models\User;
use Illuminate\Support\Facades\{File, Schema};

uses()->group('commands');

beforeEach(function (): void {
    $this->templatePath = 'tests/Templates/Console/Commands/Make/ActionMakeCommand/';

    File::deleteDirectory(app_path("Actions"));
    File::deleteDirectory(app()->basePath("tests/Feature/Actions"));

    Schema::shouldReceive('getColumnListing')
        ->with('users')
        ->andReturn([
            'id',
            'name',
            'cpf',
            'email',
            'cell_phone',
            'password',
            'is_active',
            'status',
            'confirmation_token',
            'birth_date',
            'created_at',
            'updated_at',
        ]);
});

it('should run the command successfully', function (): void {
    $this->artisan(ActionMakeCommand::class, ['name' => 'ActionTest'])
        ->assertSuccessful();
});

it('should create action with test', function (string $action, string $test): void {
    $this->artisan(ActionMakeCommand::class, ['name' => $action])
        ->assertSuccessful();

    expect(File::exists($actionPath = app_path("Actions/{$action}.php")))->toBeTrue()
        ->and(File::get($actionPath))->toEqual(File::get("{$this->templatePath}{$action}.php"))
        ->and(File::exists($testPath = app()->basePath("tests/Feature/Actions/{$action}Test.php")))->toBeTrue()
        ->and(File::get($testPath))->toEqual(File::get("{$this->templatePath}{$test}.php"));
})->with([
    'only name' => ['SomeAction', 'Tests/SomeActionTest'],
    'namespace and name' => ['Ddr/SomeAction', 'Ddr/Tests/SomeActionTest'],
]);

it("should create action without test when 'without-test' option given", function (string $action): void {
    $this->artisan(ActionMakeCommand::class, [
        'name' => $action,
        '--without-test' => true,
    ])->assertSuccessful();

    expect(File::exists($actionPath = app_path("Actions/{$action}.php")))->toBeTrue()
        ->and(File::get($actionPath))->toEqual(File::get("{$this->templatePath}{$action}.php"))
        ->and(File::exists(app()->basePath("tests/Feature/Actions/{$action}Test.php")))->toBeFalse();
})->with([
    'only name' => ['SomeAction'],
    'namespace and name' => ['Ddr/SomeAction'],
]);

it('should show error when action has already been created', function (string $action): void {
    $this->artisan(ActionMakeCommand::class, ['name' => $action])
        ->assertSuccessful();

    $this->artisan(ActionMakeCommand::class, ['name' => $action])
        ->assertExitCode(0);
})->with([
    'only name' => ['SomeAction'],
    'namespace and name' => ['Ddr/SomeAction'],
]);

it('should show error when name not informed', function (): void {
    $this->artisan(ActionMakeCommand::class)
        ->expectsQuestion('What should the action be named?', '')
        ->assertExitCode(0);
});

it('should show error if trying to create a crud action without passing model', function (string $type): void {
    $this->artisan(ActionMakeCommand::class, [
        'name' => 'SomeAction',
        "--{$type}" => true,
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain(__('validation.required', ['attribute' => 'model']));
})->with(['create', 'update', 'delete']);

it('should create a crud action', function (string $type): void {
    $actionType = str($type)->ucfirst();

    $this->artisan(ActionMakeCommand::class, [
        'name' => "{$actionType}Action",
        "--{$type}" => true,
        '--model' => User::class,
    ])->assertSuccessful();

    expect(File::exists($actionPath = app_path("Actions/{$actionType}Action.php")))->toBeTrue()
        ->and(File::get($actionPath))->toEqual(File::get("{$this->templatePath}{$actionType}Action.php"))
        ->and(File::exists($testPath = app()->basePath("tests/Feature/Actions/{$actionType}ActionTest.php")))->toBeTrue()
        ->and(File::get($testPath))->toEqual(File::get("{$this->templatePath}/Tests/{$actionType}ActionTest.php"));
})->with(['create', 'update', 'delete']);
