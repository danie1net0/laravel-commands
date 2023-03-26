<?php

namespace Ddr\LaravelCommands\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'name',
        'cpf',
        'email',
        'cell_phone',
        'password',
        'is_active',
        'status',
        'confirmation_token',
        'birth_date',
    ];
}
