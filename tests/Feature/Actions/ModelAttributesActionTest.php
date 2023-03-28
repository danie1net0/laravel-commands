<?php

use Ddr\LaravelCommands\Actions\ModelAttributesAction;
use Illuminate\Support\Facades\Schema;

it('should return the model attributes', function (array $dateAttributes): void {
    Schema::shouldReceive('getColumnListing')
        ->once()
        ->with($table = 'users')
        ->andReturn([
            'id',
            'name',
            'email',
            ...$dateAttributes,
        ]);

    $attributes = (new ModelAttributesAction())->execute($table);

    expect($attributes)->toEqual([
        'id',
        'name',
        'email',
    ]);
})->with([
    'only deleted_at' => [['deleted_at']],
    'only created_at' => [['created_at']],
    'only updated_at' => [['updated_at']],
    'created_at and updated_at' => [['created_at', 'updated_at']],
    'created_at and deleted_at' => [['created_at', 'deleted_at']],
    'updated_at and deleted_at' => [['updated_at', 'deleted_at']],
    'created_at, updated_at and deleted_at' => [['created_at', 'updated_at', 'deleted_at']],
]);
