<?php

namespace App\Actions;

use Ddr\LaravelCommands\Tests\Models\User;

class CreateAction
{
    public function execute(array $params): User
    {
        return User::query()->create($params);
    }
}
