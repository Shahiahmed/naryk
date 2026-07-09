<?php

namespace App\Filament\Concerns;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TaxonomyTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('term.name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('term.slug')
                    ->label('Слаг')
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('posts_count')
                    ->label('Постов')
                    ->counts('posts')
                    ->badge()
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
