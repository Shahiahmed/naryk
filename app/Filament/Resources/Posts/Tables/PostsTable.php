<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['categories.term', 'tags.term']))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                ImageColumn::make('post_image')
                    ->label('Обложка')
                    ->disk('public')
                    ->getStateUsing(fn (Post $record): ?string => Post::imagePath($record->post_image)),

                TextColumn::make('post_title')
                    ->label('Заголовок')
                    ->searchable()
                    ->wrap()
                    ->limit(80),

                TextColumn::make('ads_show')
                    ->label('Баннер')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'hide' => 'Скрыт',
                        'iworld' => 'Its-World',
                        default => 'Freedom',
                    })
                    ->color(fn (?string $state): string => $state === 'hide' ? 'gray' : 'success'),

                IconColumn::make('pr_news')
                    ->label('PR')
                    ->boolean(),

                TextColumn::make('categories.term.name')
                    ->label('Категория')
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('tags.term.name')
                    ->label('Теги')
                    ->badge()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->placeholder('—'),

                TextColumn::make('post_hits')
                    ->label('Просмотры')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('categories')
                    ->label('Категория')
                    ->relationship('categories', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => (string) $record->name)
                    ->multiple()
                    ->preload(),

                SelectFilter::make('post_status')
                    ->label('Статус')
                    ->options([
                        'publish' => 'Опубликован',
                        'draft' => 'Черновик',
                    ]),

                SelectFilter::make('post_visibility')
                    ->label('Видимость')
                    ->options([
                        'public' => 'Публичный',
                        'private' => 'Приватный',
                    ]),

                TernaryFilter::make('marquee')
                    ->label('В бегущей строке')
                    ->queries(
                        true: fn ($query) => $query->where('marquee', 'yes'),
                        false: fn ($query) => $query->where(fn ($q) => $q->where('marquee', '!=', 'yes')->orWhereNull('marquee')),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->defaultSort('id', 'desc')
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
