<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Concerns\CreatesTerm;
use App\Filament\Resources\Tags\TagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    use CreatesTerm;

    protected static string $resource = TagResource::class;
}
