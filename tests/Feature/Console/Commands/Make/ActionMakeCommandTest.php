<?php

use Ddr\LaravelCommands\Console\Commands\Make\ActionMakeCommand;
use Ddr\LaravelCommands\Tests\Models\User;
use Illuminate\Support\Facades\{File, Schema};

uses()->group('commands');

beforeEach(function (): void {
    File::deleteDirectory(getPath('Actions'));
    File::deleteDirectory(getPath('tests/Feature/Actions', true));

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

it('should create action with test', function (string $action, string $stub): void {
    $this->artisan(ActionMakeCommand::class, ['name' => $action])
        ->assertSuccessful();

    $actionPath = getPath("Actions/{$action}.php");
    $testPath = getPath("tests/Feature/Actions/{$action}Test.php", true);

    expect($actionPath)->exists()
        ->and($actionPath)->toEqualFile("tests/stubs/actions/{$stub}.class.stub")
        ->and($testPath)->exists()
        ->and($testPath)->toEqualFile("tests/stubs/actions/{$stub}.test.stub");
})->with([
    'with namespace' => ['Ddr/SomeAction', 'action-with-namespace'],
    'without namespace' => ['SomeAction', 'some-action'],
]);

it("should create action without test when 'without-test' option given", function (string $action, string $stub): void {
    $this->artisan(ActionMakeCommand::class, [
        'name' => $action,
        '--without-test' => true,
    ])->assertSuccessful();

    $actionPath = getPath("Actions/{$action}.php");
    $testPath = getPath("tests/Feature/Actions/{$stub}.test.stub", true);

    expect($actionPath)->exists()
        ->and($actionPath)->toEqualFile("tests/stubs/actions/{$stub}.class.stub")
        ->and($testPath)->not->exists();
})->with([
    'with namespace' => ['Ddr/SomeAction', 'action-with-namespace'],
    'without namespace' => ['SomeAction', 'some-action'],
]);

it('should show error when action has already been created', function (string $action): void {
    $this->artisan(ActionMakeCommand::class, ['name' => $action])
        ->assertSuccessful();

    $this->artisan(ActionMakeCommand::class, ['name' => $action])
        ->assertExitCode(0);
})->with([
    'with namespace' => ['Ddr/SomeAction'],
    'without namespace' => ['SomeAction'],
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

    $actionPath = getPath("Actions/{$actionType}Action.php");
    $testPath = getPath("tests/Feature/Actions/{$actionType}ActionTest.php", true);

    expect($actionPath)->exists()
        ->and($actionPath)->toEqualFile("tests/stubs/actions/{$type}-action.class.stub")
        ->and($testPath)->exists()
        ->and($testPath)->toEqualFile("tests/stubs/actions/{$type}-action.test.stub");
})->with(['create', 'update', 'delete']);
