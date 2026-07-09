<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Роль')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('permissions_count')
                    ->label('Прав')
                    ->counts('permissions')
                    ->badge()
                    ->color('info'),

                TextColumn::make('users_count')
                    ->label('Пользователей')
                    ->counts('users')
                    ->badge(),
            ])
            ->defaultSort('id')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
