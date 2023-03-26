<?php

namespace App\Actions;

use Ddr\LaravelCommands\Tests\Models\User;

class DeleteAction
{
    public function execute(User $user): bool
    {
        return $user->delete();
    }
}
