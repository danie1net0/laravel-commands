<?php

namespace App\Actions;

use Ddr\LaravelCommands\Tests\Models\User;

class UpdateAction
{
    public function execute(User $user, array $params): User
    {
        return tap($user)->update($params);
    }
}
