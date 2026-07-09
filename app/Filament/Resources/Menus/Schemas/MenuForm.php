<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Название меню')
                            ->required()
                            ->maxLength(191)
                            ->unique(ignoreRecord: true)
                            ->helperText('Вёрстка сайта ищет меню по этому имени: header, footer.'),
                    ]),

                Section::make('Пункты меню')
                    ->description('Перетаскивайте, чтобы изменить порядок.')
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->relationship()
                            ->orderColumn('sort')
                            ->reorderable()
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->collapsible()
                            ->addActionLabel('Добавить пункт')
                            ->schema([
                                TextInput::make('label')
                                    ->label('Название')
                                    ->required()
                                    ->maxLength(191),

                                TextInput::make('link')
                                    ->label('Ссылка')
                                    ->required()
                                    ->maxLength(191)
                                    ->placeholder('/category/qarzhy'),

                                TextInput::make('class')
                                    ->label('CSS-класс')
                                    ->maxLength(191)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
