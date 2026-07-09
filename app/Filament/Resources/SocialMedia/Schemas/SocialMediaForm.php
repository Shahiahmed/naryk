<?php

namespace App\Filament\Resources\SocialMedia\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SocialMediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(191)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Слаг')
                            ->required()
                            ->maxLength(191)
                            ->unique(ignoreRecord: true),

                        TextInput::make('url')
                            ->label('Ссылка')
                            ->url()
                            ->maxLength(191),

                        TextInput::make('icon')
                            ->label('Иконка')
                            ->placeholder('fab fa-facebook')
                            ->helperText('Класс Font Awesome, как в старой админке.')
                            ->maxLength(191),
                    ]),
            ]);
    }
}
