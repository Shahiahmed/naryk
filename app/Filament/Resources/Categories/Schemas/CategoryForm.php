<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Concerns\TaxonomyForm;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return TaxonomyForm::configure($schema);
    }
}
