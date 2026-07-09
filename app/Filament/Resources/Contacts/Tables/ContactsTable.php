<?php

namespace App\Filament\Resources\Contacts\Tables;

use App\Models\Contact;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    // The unread ones should catch the eye in a long list.
                    ->weight(fn (Contact $record): ?string => $record->status === 'unread' ? 'bold' : null),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('subject')
                    ->label('Тема')
                    ->searchable()
                    ->limit(50)
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'unread' ? 'Новое' : 'Прочитано')
                    ->color(fn (string $state): string => $state === 'unread' ? 'warning' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Получено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'unread' => 'Новое',
                        'read' => 'Прочитано',
                    ]),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('Читать')
                    ->modalHeading(fn (Contact $record): string => $record->subject ?: 'Сообщение')
                    ->schema([
                        TextEntry::make('name')->label('Имя'),
                        TextEntry::make('email')->label('E-mail')->copyable(),
                        TextEntry::make('created_at')->label('Получено')->dateTime('d.m.Y H:i'),
                        TextEntry::make('message')->label('Сообщение')->columnSpanFull(),
                    ])
                    ->after(fn (Contact $record) => $record->markAsRead()),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Сообщений пока нет')
            ->emptyStateDescription('Они появятся, когда на сайте заработает форма обратной связи.');
    }
}
