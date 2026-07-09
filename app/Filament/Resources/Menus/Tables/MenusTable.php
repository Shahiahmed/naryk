<?php

namespace App\Filament\Resources\Menus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Название')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Пунктов')
                    ->counts('items')
                    ->badge()
                    ->color('info'),

                TextColumn::make('items.label')
                    ->label('Пункты')
                    ->badge()
                    ->limitList(4)
                    ->expandableLimitedList()
                    ->placeholder('пусто'),
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
