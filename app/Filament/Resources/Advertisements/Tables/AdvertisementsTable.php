<?php

namespace App\Filament\Resources\Advertisements\Tables;

use App\Models\Advertisement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class AdvertisementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                ImageColumn::make('image')
                    ->label('Баннер')
                    ->disk('public')
                    ->getStateUsing(fn (Advertisement $record): ?string => Advertisement::imagePath($record->image))
                    ->height(40),

                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('size')
                    ->label('Размер')
                    ->placeholder('—'),

                TextColumn::make('url')
                    ->label('Ссылка')
                    ->limit(40)
                    ->url(fn (Advertisement $record): ?string => $record->url)
                    ->openUrlInNewTab()
                    ->toggleable(),

                TextColumn::make('placements.name')
                    ->label('Размещения')
                    ->badge()
                    ->placeholder('не используется'),

                ToggleColumn::make('active')
                    ->label('Активен'),
            ])
            ->defaultSort('id')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
