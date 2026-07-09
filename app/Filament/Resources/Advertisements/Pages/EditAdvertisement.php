<?php

namespace App\Filament\Resources\Advertisements\Pages;

use App\Filament\Concerns\SplitsAdSize;
use App\Filament\Resources\Advertisements\AdvertisementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdvertisement extends EditRecord
{
    use SplitsAdSize;

    protected static string $resource = AdvertisementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
