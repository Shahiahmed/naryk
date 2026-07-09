<?php

namespace App\Filament\Resources\AdPlacements\Schemas;

use App\Models\Advertisement;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdPlacementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Размещение')
                            ->disabled()
                            ->helperText('Слот задан вёрсткой сайта и не переименовывается.'),

                        // Not a column: the link lives in the pivot, and the page
                        // syncs it after saving.
                        Select::make('advertisement_id')
                            ->label('Баннер')
                            ->options(fn (): array => Advertisement::query()
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Advertisement $ad): array => [
                                    $ad->id => $ad->size ? "{$ad->name} ({$ad->size})" : $ad->name,
                                ])
                                ->all())
                            ->placeholder('Без баннера')
                            ->dehydrated(false),

                        Toggle::make('active')
                            ->label('Активно'),
                    ]),
            ]);
    }
}
