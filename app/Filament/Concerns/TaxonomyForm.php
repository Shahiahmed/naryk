<?php

namespace App\Filament\Concerns;

use App\Models\TermTaxonomy;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Categories and tags are both rows in `term_taxonomies`, but their name and
 * slug live on the related `terms` row. The pages persist them; this only
 * builds the fields.
 */
class TaxonomyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(200)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                        if ($operation === 'create' && filled($state)) {
                            $set('slug', Str::slug($state));
                        }
                    }),

                TextInput::make('slug')
                    ->label('Слаг')
                    ->required()
                    ->maxLength(200)
                    ->rule(fn (?TermTaxonomy $record) => Rule::unique('terms', 'slug')->ignore($record?->term_id)),
            ]);
    }
}
