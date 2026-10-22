<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Concerns\HandlesPostImage;
use App\Filament\Resources\Pages\PageResource;
use App\Support\Embeds;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    // Renamed, because this page also prepares the embeds for the editor.
    use HandlesPostImage {
        mutateFormDataBeforeFill as unpackImageBeforeFill;
    }

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Before the editor's own cast runs, or the iframes are gone: it has no
     * node for them. See App\Support\Embeds.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = $this->unpackImageBeforeFill($data);

        $data['post_content'] = Embeds::toEditor($data['post_content'] ?? null);

        return $data;
    }
}
