<?php

namespace App\Filament\Concerns;

use App\Models\Term;
use Illuminate\Support\Str;

trait CreatesTerm
{
    /**
     * `name` and `slug` are not columns on term_taxonomies — create the term
     * first and hand back its id, or Eloquent tries to persist them.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $term = Term::create([
            'name' => $data['name'],
            'slug' => filled($data['slug'] ?? null) ? $data['slug'] : Str::slug($data['name']),
        ]);

        unset($data['name'], $data['slug']);

        $data['term_id'] = $term->getKey();

        return $data;
    }
}
