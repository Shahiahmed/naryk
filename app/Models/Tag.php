<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Tag extends TermTaxonomy
{
    public const TAXONOMY = 'tag';

    protected static function booted(): void
    {
        static::addGlobalScope('tag', function (Builder $query): void {
            $query->where('term_taxonomies.taxonomy', self::TAXONOMY)->whereHas('term');
        });

        static::creating(function (self $tag): void {
            $tag->taxonomy = self::TAXONOMY;
        });
    }
}
