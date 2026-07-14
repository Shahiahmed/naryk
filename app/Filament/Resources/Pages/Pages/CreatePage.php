<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Concerns\HandlesPostImage;
use App\Filament\Resources\Pages\PageResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    // Renamed, because this page adds the author on top of it.
    use HandlesPostImage {
        mutateFormDataBeforeCreate as packImageBeforeCreate;
    }

    protected static string $resource = PageResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->packImageBeforeCreate($data);

        $data['post_author'] ??= Filament::auth()->id();

        return $data;
    }
}
