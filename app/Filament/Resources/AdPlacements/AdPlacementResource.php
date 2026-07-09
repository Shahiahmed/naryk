<?php

namespace App\Filament\Resources\AdPlacements;

use App\Filament\Resources\AdPlacements\Pages\EditAdPlacement;
use App\Filament\Resources\AdPlacements\Pages\ListAdPlacements;
use App\Filament\Resources\AdPlacements\Schemas\AdPlacementForm;
use App\Filament\Resources\AdPlacements\Tables\AdPlacementsTable;
use App\Models\AdPlacement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AdPlacementResource extends Resource
{
    protected static ?string $model = AdPlacement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Реклама';

    protected static ?string $navigationLabel = 'Размещения';

    protected static ?string $modelLabel = 'размещение';

    protected static ?string $pluralModelLabel = 'Размещения';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    /**
     * Placements are wired into the theme, so the old admin never let anyone
     * add or remove them — and `ad_placements.id` has no AUTO_INCREMENT, so an
     * insert would fail anyway.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AdPlacementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdPlacementsTable::configure($table);
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
            'index' => ListAdPlacements::route('/'),
            'edit' => EditAdPlacement::route('/{record}/edit'),
        ];
    }
}
