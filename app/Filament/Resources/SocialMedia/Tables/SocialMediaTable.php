<?php

namespace App\Filament\Resources\SocialMedia\Tables;

use App\Models\SocialMedia;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SocialMediaTable
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
                    ->searchable()
                    ->sortable(),

                TextColumn::make('icon')
                    ->label('Иконка')
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('url')
                    ->label('Ссылка')
                    ->url(fn (SocialMedia $record): ?string => $record->url)
                    ->openUrlInNewTab()
                    ->limit(40),
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
