<?php

namespace App\Filament\Resources\Tags\Schemas;

use App\Filament\Concerns\TaxonomyForm;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return TaxonomyForm::configure($schema);
    }
}
