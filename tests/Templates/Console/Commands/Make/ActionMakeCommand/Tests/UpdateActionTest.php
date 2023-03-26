<?php

use App\Actions\UpdateAction;
use Ddr\LaravelCommands\Tests\Models\User;

use function Pest\Laravel\assertDatabaseHas;

uses()->group('actions', 'update-action', );

it('should update a user', function (): void {
    /** @var User $user */
    $user = User::factory()->create();

    $userData = User::factory()
        ->make()
        ->toArray();

    $updatedUser = (new UpdateAction())->execute($user, $userData);

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'name' => $updatedUser->name,
        'cpf' => $updatedUser->cpf,
        'email' => $updatedUser->email,
        'cell_phone' => $updatedUser->cell_phone,
        'password' => $updatedUser->password,
        'is_active' => $updatedUser->is_active,
        'status' => $updatedUser->status,
        'confirmation_token' => $updatedUser->confirmation_token,
        'birth_date' => $updatedUser->birth_date,
    ]);
});
