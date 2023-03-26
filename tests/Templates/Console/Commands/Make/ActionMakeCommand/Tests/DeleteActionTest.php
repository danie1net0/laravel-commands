<?php

use App\Actions\DeleteAction;
use Ddr\LaravelCommands\Tests\Models\User;

use function Pest\Laravel\assertDatabaseMissing;

uses()->group('actions', 'delete-action');

it('should delete a user', function (): void {
    /** @var User $user */
    $user = User::factory()->create();

    $isDeleted = (new DeleteAction())->execute($user);

    expect($isDeleted)->toBeTrue();

    assertDatabaseMissing(User::class, [
        'id' => $user->id,
    ]);
});
