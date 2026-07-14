<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Concerns\HandlesPostImage;
use App\Filament\Resources\Posts\PostResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    // Renamed, because this page adds the author on top of it.
    use HandlesPostImage {
        mutateFormDataBeforeCreate as packImageBeforeCreate;
    }

    protected static string $resource = PostResource::class;

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
