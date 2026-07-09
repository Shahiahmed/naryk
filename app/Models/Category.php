<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Category extends TermTaxonomy
{
    public const TAXONOMY = 'category';

    /**
     * Seven legacy rows point at a `terms` record that no longer exists.
     * They carry no posts, so hide them rather than clean up the client's data.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('category', function (Builder $query): void {
            $query->where('term_taxonomies.taxonomy', self::TAXONOMY)->whereHas('term');
        });

        static::creating(function (self $category): void {
            $category->taxonomy = self::TAXONOMY;
        });
    }
}
