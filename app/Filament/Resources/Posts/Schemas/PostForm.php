<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\TermTaxonomy;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Материал')
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
                            ->helperText('Адрес статьи: /news/слаг')
                            ->required()
                            ->maxLength(200)
                            ->unique(ignoreRecord: true),

                        RichEditor::make('post_content')
                            ->label('Текст')
                            ->required(),

                        // 333 of the 363 non-empty summaries hold HTML.
                        RichEditor::make('post_summary')
                            ->label('Краткое описание')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'superscript', 'subscript']),

                        FileUpload::make('post_image')
                            ->label('Обложка')
                            ->image()
                            ->disk('public')
                            // Legacy layout is images/<year>/<month>/<file>, but the
                            // column stores the path without the images/ prefix.
                            ->directory('images/'.now()->format('Y/m'))
                            ->imageEditor()
                            /*
                             * A camera photo runs to 3-5 MB and PHP's stock
                             * upload_max_filesize is 2M, so the upload used to
                             * fail with no message at all. State the limit and
                             * let Filament say so when it is passed. The server
                             * limit has to be raised to match — see DEPLOY.md.
                             */
                            ->maxSize(10 * 1024)
                            ->helperText('JPG, PNG или WebP, до 10 МБ. Вертикальные фото тоже подходят — выберите вид карточки ниже.'),
                        // The images/ prefix is translated by HandlesPostImage
                        // on the page, not here: see the note in that trait.

                        Select::make('show_image')
                            ->label('Вид карточки в ленте')
                            ->options([
                                Post::LAYOUT_WIDE => 'Горизонтальная обложка — заголовок и лид под ней',
                                Post::LAYOUT_TALL => 'Вертикальная обложка — заголовок поверх, без лида',
                                Post::LAYOUT_TEXT => 'Без обложки — заголовок и лид',
                            ])
                            // Two legacy rows hold `public` and NULL; both mean wide.
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                Post::LAYOUT_TALL => Post::LAYOUT_TALL,
                                Post::LAYOUT_TEXT => Post::LAYOUT_TEXT,
                                default => Post::LAYOUT_WIDE,
                            })
                            ->default(Post::LAYOUT_WIDE)
                            ->selectablePlaceholder(false)
                            ->required(),
                    ]),

                Section::make('Публикация')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('post_status')
                            ->label('Статус')
                            ->options([
                                'publish' => 'Опубликован',
                                'draft' => 'Черновик',
                            ])
                            ->default('publish')
                            ->selectablePlaceholder(false)
                            ->required(),

                        Select::make('post_visibility')
                            ->label('Видимость')
                            ->options([
                                'public' => 'Публичный',
                                'private' => 'Приватный',
                            ])
                            ->default('public')
                            ->selectablePlaceholder(false)
                            ->required(),

                        Select::make('comment_status')
                            ->label('Комментарии')
                            ->options([
                                'open' => 'Открыты',
                                'closed' => 'Закрыты',
                            ])
                            ->default('open')
                            ->selectablePlaceholder(false)
                            ->required(),

                        TextInput::make('post_hits')
                            ->label('Просмотры')
                            ->numeric()
                            ->minValue(0)
                            ->default(1),

                        Select::make('post_author')
                            ->label('Автор')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Рубрикация')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        Select::make('categories')
                            ->label('Категории')
                            ->relationship(
                                name: 'categories',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->with('term'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Category $record): string => (string) $record->name)
                            ->multiple()
                            ->preload(),

                        // 2281 tags: search in the database instead of shipping them all.
                        Select::make('tags')
                            ->label('Теги')
                            ->relationship('tags', 'id')
                            ->getSearchResultsUsing(fn (string $search): array => self::searchTaxonomy(Tag::query(), $search))
                            ->getOptionLabelsUsing(fn (array $values): array => self::labelsFor(Tag::query(), $values))
                            ->multiple()
                            ->searchable(),
                    ]),

                Section::make('Реклама и оформление')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('ads_show')
                            ->label('Баннер в статье')
                            ->options([
                                '' => 'Freedom (по умолчанию)',
                                'iworld' => 'Its-World',
                                'hide' => 'Не показывать',
                            ])
                            // Half the rows store NULL rather than an empty string.
                            ->formatStateUsing(fn (?string $state): string => $state ?? '')
                            ->default('')
                            ->selectablePlaceholder(false),

                        // `pr_news` is toggled straight from the post list, as it
                        // was in the old admin — it has no field on this form.
                        Toggle::make('marquee')
                            ->label('В бегущей строке'),
                    ]),

                Section::make('SEO')
                    ->columnSpan(2)
                    ->schema([
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(2),

                        Textarea::make('meta_keyword')
                            ->label('Meta Keyword')
                            ->rows(2),
                    ]),
            ]);
    }

    /**
     * The label lives on `terms`, so it cannot be searched through Filament's
     * default titleAttribute query.
     *
     * @param  Builder<TermTaxonomy>  $query
     * @return array<int, string>
     */
    protected static function searchTaxonomy(Builder $query, string $search): array
    {
        return $query
            ->whereHas('term', fn (Builder $term): Builder => $term->where('name', 'like', "%{$search}%"))
            ->with('term')
            ->limit(50)
            ->get()
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @param  Builder<TermTaxonomy>  $query
     * @param  array<int, int|string>  $values
     * @return array<int, string>
     */
    protected static function labelsFor(Builder $query, array $values): array
    {
        return $query
            ->whereIn('term_taxonomies.id', $values)
            ->with('term')
            ->get()
            ->pluck('name', 'id')
            ->all();
    }
}
