<?php

namespace App\Filament\RichContent;

use App\Support\Embeds;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentCustomBlock;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * The editor-side face of an iframe: see App\Support\Embeds for why the
 * database never stores this block, only the iframe it stands for.
 */
class EmbedBlock extends RichContentCustomBlock
{
    public static function getId(): string
    {
        return Embeds::BLOCK_ID;
    }

    public static function getLabel(): string
    {
        return 'Встраивание (iframe, видео)';
    }

    /**
     * Wires the block into a post or page body.
     */
    public static function attachTo(RichEditor $editor): RichEditor
    {
        return $editor
            ->customBlocks([static::class])
            ->dehydrateStateUsing(fn (?string $state): ?string => Embeds::fromEditor($state))
            ->helperText('Код iframe можно вставить прямо в текст отдельной строкой — при сохранении он станет встраиванием. Или кнопка «+» на панели → «Встраивание».');
    }

    public static function configureEditorAction(Action $action): Action
    {
        return $action
            ->modalDescription('Вставьте код iframe целиком или просто ссылку — YouTube, Vimeo, страницу сайта.')
            ->schema([
                Textarea::make('src')
                    ->label('Код или ссылка')
                    ->placeholder('<iframe src="https://..."></iframe>')
                    ->rows(3)
                    ->required()
                    ->live(onBlur: true)
                    // Pasted code is split into its link and height right away,
                    // so the editor sees what will actually be kept.
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        $embed = Embeds::parse($state);

                        if ($embed === null) {
                            return;
                        }

                        $set('src', $embed['src']);

                        if ($embed['height'] !== null) {
                            $set('height', $embed['height']);
                        }
                    })
                    ->dehydrateStateUsing(fn (?string $state): ?string => Embeds::parse($state)['src'] ?? $state)
                    ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                        if (Embeds::parse((string) $value) === null) {
                            $fail('Нужна ссылка https://… или код iframe с такой ссылкой.');
                        }
                    }),

                TextInput::make('height')
                    ->label('Высота, px')
                    ->numeric()
                    ->minValue(50)
                    ->maxValue(5000)
                    ->placeholder('авто')
                    ->helperText('Для видео не нужна — оно всегда 16:9. Для страниц сайта по умолчанию подстраивается под содержимое.'),
            ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function getPreviewLabel(array $config): string
    {
        $src = (string) ($config['src'] ?? '');

        return 'Встраивание: '.Str::limit(preg_replace('~^https?://~i', '', $src), 60);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function toPreviewHtml(array $config): ?string
    {
        $embed = Embeds::parse((string) ($config['src'] ?? ''));

        if ($embed === null) {
            return '<p style="color:#b91c1c">Ссылка не распознана — откройте блок и проверьте её.</p>';
        }

        // The admin has none of the site's CSS, so the frame is sized inline.
        $style = Embeds::isVideo($embed['src'])
            ? 'width:100%;aspect-ratio:16/9;border:0'
            : 'width:100%;height:'.min((int) ($config['height'] ?? 0) ?: 400, 600).'px;border:0';

        return '<iframe src="'.e($embed['src']).'" style="'.$style.'" loading="lazy" allowfullscreen></iframe>';
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $data
     */
    public static function toHtml(array $config, array $data): ?string
    {
        return Embeds::iframe($config);
    }
}
