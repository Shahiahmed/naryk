<?php

namespace App\Filament\Resources\Advertisements;

use App\Filament\Resources\Advertisements\Pages\CreateAdvertisement;
use App\Filament\Resources\Advertisements\Pages\EditAdvertisement;
use App\Filament\Resources\Advertisements\Pages\ListAdvertisements;
use App\Filament\Resources\Advertisements\Schemas\AdvertisementForm;
use App\Filament\Resources\Advertisements\Tables\AdvertisementsTable;
use App\Models\Advertisement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AdvertisementResource extends Resource
{
    protected static ?string $model = Advertisement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Реклама';

    protected static ?string $navigationLabel = 'Баннеры';

    protected static ?string $modelLabel = 'баннер';

    protected static ?string $pluralModelLabel = 'Баннеры';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AdvertisementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdvertisementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdvertisements::route('/'),
            'create' => CreateAdvertisement::route('/create'),
            'edit' => EditAdvertisement::route('/{record}/edit'),
        ];
    }
}
