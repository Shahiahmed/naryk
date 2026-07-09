<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Фото')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(asset('favicon.ico')),

                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('username')
                    ->label('Логин')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('roles.name')
                    ->label('Роли')
                    ->badge()
                    ->placeholder('нет роли'),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Активен',
                        'deactive' => 'Отключён',
                        default => $state,
                    })
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активен',
                        'deactive' => 'Отключён',
                    ]),

                SelectFilter::make('roles')
                    ->label('Роль')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
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
