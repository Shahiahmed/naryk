<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Concerns\EditsTerm;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    use EditsTerm;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
