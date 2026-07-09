<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use App\Models\Advertisement;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdvertisementForm
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
                            ->columnSpanFull(),

                        FileUpload::make('image')
                            ->label('Изображение')
                            ->image()
                            ->disk('public')
                            ->directory('ad')
                            // The column stores a bare filename.
                            ->formatStateUsing(fn (?string $state): ?string => Advertisement::imagePath($state))
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? basename($state) : null)
                            ->columnSpanFull(),

                        TextInput::make('width')
                            ->label('Ширина')
                            ->numeric()
                            ->suffix('px'),

                        TextInput::make('height')
                            ->label('Высота')
                            ->numeric()
                            ->suffix('px'),

                        TextInput::make('url')
                            ->label('Ссылка')
                            ->url()
                            ->maxLength(191)
                            ->columnSpanFull(),

                        Toggle::make('active')
                            ->label('Активен')
                            ->default(true),
                    ]),
            ]);
    }
}
