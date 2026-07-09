<?php

namespace App\Filament\Resources\Tags\Tables;

use App\Filament\Concerns\TaxonomyTable;
use Filament\Tables\Table;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return TaxonomyTable::configure($table);
    }
}
