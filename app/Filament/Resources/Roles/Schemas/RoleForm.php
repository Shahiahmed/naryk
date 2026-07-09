<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),

                        TextInput::make('guard_name')
                            ->label('Guard')
                            ->default('web')
                            ->required()
                            ->maxLength(191),
                    ]),

                Section::make('Права')
                    ->description('Схема прав унаследована от старого сайта: действие-сущность, например read-posts.')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->hiddenLabel()
                            ->relationship(
                                name: 'permissions',
                                titleAttribute: 'name',
                                // Order by entity so the actions of one entity sit
                                // together instead of scattering alphabetically.
                                modifyQueryUsing: fn (Builder $query): Builder => $query
                                    ->orderByRaw("SUBSTRING(name, LOCATE('-', name) + 1), name"),
                            )
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(4)
                            ->gridDirection('row'),
                    ]),
            ]);
    }
}
