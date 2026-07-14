<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ManageSettings extends Page
{
    protected string $view = 'filament.pages.manage-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Настройки сайта';

    protected static ?string $title = 'Настройки сайта';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Filament::auth()->user()?->hasAnyRole(['superadmin', 'admin']) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(Setting::tree());
    }

    public function save(): void
    {
        Setting::persist($this->form->getState());

        Notification::make()->title('Настройки сохранены')->success()->send();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make()->tabs([
                    self::informationTab(),
                    self::contactTab(),
                    self::propertiesTab(),
                    self::sponsorTab(),
                    self::configTab(),
                    self::permalinksTab(),
                ]),
            ]);
    }

    protected static function informationTab(): Tab
    {
        return Tab::make('Информация')
            ->columns(2)
            ->schema([
                TextInput::make('site_information.company_name')->label('Название компании'),
                TextInput::make('site_information.siteurl')->label('Адрес сайта')->url(),
                TextInput::make('site_information.sitename')->label('Заголовок сайта')->columnSpanFull(),
                Textarea::make('site_information.sitedescription')->label('Описание сайта')->rows(2)->columnSpanFull(),
                Textarea::make('site_information.metakeyword')->label('Ключевые слова')->rows(2)->columnSpanFull(),
            ]);
    }

    protected static function contactTab(): Tab
    {
        return Tab::make('Контакты')
            ->columns(2)
            ->schema([
                TextInput::make('site_information.street')->label('Улица')->columnSpanFull(),
                TextInput::make('site_information.city')->label('Город'),
                TextInput::make('site_information.postal_code')->label('Индекс'),
                TextInput::make('site_information.state')->label('Область'),
                TextInput::make('site_information.country')->label('Страна'),
                Textarea::make('site_information.fulladdress')->label('Полный адрес')->rows(2),
                TextInput::make('site_information.sitephone')->label('Телефон'),
                TextInput::make('site_information.siteemail')->label('E-mail')->email(),
                Textarea::make('site_information.contactdescription')->label('Описание формы обратной связи')->rows(2)->columnSpanFull(),

                // The five the site shows, in the order it shows them.
                TextInput::make('social_media.telegram')->label('Telegram'),
                TextInput::make('social_media.instagram')->label('Instagram'),
                TextInput::make('social_media.tiktok')->label('TikTok'),
                TextInput::make('social_media.threads')->label('Threads'),
                TextInput::make('social_media.facebook')->label('Facebook')->columnSpanFull(),
            ]);
    }

    protected static function propertiesTab(): Tab
    {
        return Tab::make('Логотипы')
            ->columns(2)
            ->schema([
                self::assetUpload('logo_image.logowebsite', 'Логотип (шапка)'),
                self::assetUpload('logo_image.logowebsite_footer', 'Логотип (подвал)'),
                self::assetUpload('logo_image.favicon', 'Favicon'),
                self::assetUpload('logo_image.ogimage', 'Open Graph'),
                self::assetUpload('logo_image.logodashboard', 'Логотип админки'),
                self::assetUpload('logo_image.logoauth', 'Логотип на входе'),
            ]);
    }

    /**
     * The sponsor logo sits in the navigation on the live site. It is an ad,
     * not chrome, so it belongs in the settings rather than the template —
     * changing the sponsor or its UTM tags must not need a developer.
     */
    protected static function sponsorTab(): Tab
    {
        return Tab::make('Спонсор')
            ->columns(2)
            ->schema([
                self::assetUpload('sponsor.logo', 'Логотип в меню')
                    ->helperText('SVG или PNG. Показывается слева от пунктов меню.'),

                TextInput::make('sponsor.url')
                    ->label('Ссылка')
                    ->url()
                    ->helperText('Вместе с UTM-метками, если они нужны.'),

                TextInput::make('sponsor.title')
                    ->label('Подпись')
                    ->helperText('Для alt и подсказки при наведении.')
                    ->columnSpanFull(),
            ]);
    }

    protected static function configTab(): Tab
    {
        return Tab::make('Конфигурация')
            ->columns(2)
            ->schema([
                TextInput::make('google.googleanalyticsid')->label('Google Analytics ID'),
                TextInput::make('site_config.analytics_view_id')->label('Analytics View ID'),
                TextInput::make('google.publisherid')->label('Publisher ID'),
                TextInput::make('google.googlesiteverification')->label('Google Site Verification'),
                TextInput::make('google.disqusshortname')->label('Disqus Short Name'),
                TextInput::make('google.mailchimp')->label('Mailchimp'),
                Textarea::make('google.googlemapcode')->label('Google Map Code')->rows(3)->columnSpanFull(),

                self::yesNoToggle('site_config.maintenance', 'Режим обслуживания'),
                self::yesNoToggle('site_config.register', 'Регистрация пользователей'),
            ]);
    }

    protected static function permalinksTab(): Tab
    {
        return Tab::make('Ссылки')
            ->schema([
                Radio::make('permalinks.permalink_type')
                    ->label('Адрес статьи')
                    ->options([
                        'post_name' => 'Слаг: /sample-post',
                        'day_and_name' => 'Дата и слаг: /2026/7/9/sample-post',
                        'month_and_name' => 'Месяц и слаг: /2026/7/sample-post',
                        'custom' => 'Свой префикс',
                    ]),

                TextInput::make('permalinks.permalink')
                    ->label('Префикс')
                    ->prefix('naryk.kz/')
                    ->suffix('/sample-post')
                    ->visible(fn (Get $get): bool => $get('permalinks.permalink_type') === 'custom'),

                Radio::make('page_permalinks.page_permalink_type')
                    ->label('Адрес страницы')
                    ->options([
                        'page_name' => 'Слаг: /sample-page',
                        'with_prefix_page' => 'С префиксом: /page/sample-page',
                    ]),

                Radio::make('category_permalinks.category_permalink_type')
                    ->label('Адрес категории')
                    ->options([
                        'category_name' => 'Слаг: /sample-category',
                        'with_prefix_category' => 'С префиксом: /category/sample-category',
                    ]),
            ]);
    }

    protected static function assetUpload(string $name, string $label): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            // `image()` alone rejects SVG, and the site logo is one.
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'image/x-icon'])
            ->disk('public')
            ->directory('assets')
            // The setting stores a bare filename.
            ->formatStateUsing(fn (?string $state): ?string => Setting::assetPath($state))
            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? basename($state) : null);
    }

    protected static function yesNoToggle(string $name, string $label): Toggle
    {
        return Toggle::make($name)
            ->label($label)
            ->formatStateUsing(fn (?string $state): bool => $state === 'y')
            ->dehydrateStateUsing(fn (bool $state): string => $state ? 'y' : 'n');
    }
}
