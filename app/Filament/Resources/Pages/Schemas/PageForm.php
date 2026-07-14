<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Страница')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('post_title')
                            ->label('Заголовок')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('post_name', Str::slug($state));
                                }
                            }),

                        TextInput::make('post_name')
                            ->label('Слаг')
                            ->helperText('Адрес страницы: /слаг')
                            ->required()
                            ->maxLength(200)
                            // Unique across the whole table: posts and pages share it.
                            ->unique(ignoreRecord: true, table: 'posts', column: 'post_name'),

                        RichEditor::make('post_summary')
                            ->label('Краткое описание')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'superscript', 'subscript']),

                        RichEditor::make('post_content')
                            ->label('Текст')
                            ->required(),
                    ]),

                Section::make('Оформление')
                    ->columnSpan(1)
                    ->schema([
                        FileUpload::make('post_image')
                            ->label('Обложка')
                            ->image()
                            ->disk('public')
                            ->directory('images/'.now()->format('Y/m'))
                            ->imageEditor()
                            ->maxSize(10 * 1024)
                            ->helperText('JPG, PNG или WebP, до 10 МБ.'),
                        // The images/ prefix is translated by HandlesPostImage
                        // on the page, not here: see the note in that trait.

                        Select::make('post_status')
                            ->label('Статус')
                            ->options([
                                'publish' => 'Опубликована',
                                'draft' => 'Черновик',
                            ])
                            ->default('publish')
                            ->selectablePlaceholder(false)
                            ->required(),

                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(2),

                        Textarea::make('meta_keyword')
                            ->label('Meta Keyword')
                            ->rows(2),
                    ]),
            ]);
    }
}
