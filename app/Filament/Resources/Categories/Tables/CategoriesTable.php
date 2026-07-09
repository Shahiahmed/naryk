<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Concerns\TaxonomyTable;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return TaxonomyTable::configure($table);
    }
}
