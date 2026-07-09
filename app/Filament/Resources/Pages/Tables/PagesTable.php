<?php

namespace App\Filament\Resources\Pages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('post_title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('post_name')
                    ->label('Слаг')
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('post_status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'publish' ? 'Опубликована' : 'Черновик')
                    ->color(fn (string $state): string => $state === 'publish' ? 'success' : 'gray'),

                TextColumn::make('updated_at')
                    ->label('Изменена')
                    ->dateTime('d.m.Y')
                    ->sortable(),
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
