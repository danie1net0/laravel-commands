<?php

namespace Ddr\LaravelCommands\Actions;

use Illuminate\Support\Facades\Schema;

class ModelAttributesAction
{
    public function execute(string $tableName): array
    {
        $modelAttributes = Schema::getColumnListing($tableName);

        $attributesToRemove = [
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        return array_diff($modelAttributes, $attributesToRemove);
    }
}
