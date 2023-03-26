<?php

use App\Actions\CreateAction;
use Ddr\LaravelCommands\Tests\Models\User;

use function Pest\Laravel\assertDatabaseHas;

uses()->group('actions', 'create-action', );

it('should create a user', function (): void {
    $userData = User::factory()
        ->make()
        ->toArray();

    $user = (new CreateAction())->execute($userData);

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'name' => $userData['name'],
        'cpf' => $userData['cpf'],
        'email' => $userData['email'],
        'cell_phone' => $userData['cell_phone'],
        'password' => $userData['password'],
        'is_active' => $userData['is_active'],
        'status' => $userData['status'],
        'confirmation_token' => $userData['confirmation_token'],
        'birth_date' => $userData['birth_date'],
    ]);
});
