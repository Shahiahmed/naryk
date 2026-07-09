<?php

namespace App\Filament\Resources\AdPlacements\Pages;

use App\Filament\Resources\AdPlacements\AdPlacementResource;
use Filament\Resources\Pages\EditRecord;

class EditAdPlacement extends EditRecord
{
    protected static string $resource = AdPlacementResource::class;

    /**
     * The banner lives in the ad_placement_advertisement pivot rather than on
     * the placement row, so it is loaded and saved by hand.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['advertisement_id'] = $this->getRecord()->advertisements()->value('advertisements.id');

        return $data;
    }

    protected function afterSave(): void
    {
        $advertisementId = $this->data['advertisement_id'] ?? null;

        $this->getRecord()->advertisements()->sync(array_filter([$advertisementId]));
    }
}
