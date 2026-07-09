<?php

namespace App\Filament\Resources\AdPlacements\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class AdPlacementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('advertisements'))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Слаг')
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('advertisements.name')
                    ->label('Баннер')
                    ->badge()
                    ->placeholder('не задан'),

                ToggleColumn::make('active')
                    ->label('Активно'),
            ])
            ->defaultSort('id')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
