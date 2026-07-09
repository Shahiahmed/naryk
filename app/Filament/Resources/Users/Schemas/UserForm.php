<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Аккаунт')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(191),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),

                        TextInput::make('username')
                            ->label('Логин')
                            ->maxLength(191),

                        TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->revealable()
                            ->maxLength(191)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText('Оставьте пустым, чтобы не менять текущий пароль.')
                            // The `hashed` cast would re-hash an empty string into a
                            // valid hash, silently locking the user out.
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),
                    ]),

                Section::make('Профиль')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('photo')
                            ->label('Фото')
                            ->image()
                            ->disk('public')
                            ->directory('avatar')
                            ->imageEditor()
                            ->columnSpanFull()
                            // The column stores a bare filename, the file lives in
                            // avatar/. Same convention as advertisements.image.
                            ->formatStateUsing(fn (?string $state): ?string => filled($state) ? User::photoPath($state) : null)
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? basename($state) : null),

                        TextInput::make('occupation')
                            ->label('Должность')
                            ->maxLength(191),

                        Select::make('status')
                            ->label('Статус')
                            ->options([
                                'active' => 'Активен',
                                'deactive' => 'Отключён',
                            ])
                            ->default('active')
                            ->selectablePlaceholder(false)
                            ->required(),

                        Textarea::make('about')
                            ->label('О себе')
                            ->maxLength(191)
                            ->columnSpanFull(),
                    ]),

                Section::make('Доступ')
                    ->schema([
                        Select::make('roles')
                            ->label('Роли')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }
}
