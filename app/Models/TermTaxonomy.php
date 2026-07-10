<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['term_id', 'taxonomy', 'description', 'parent'])]
class TermTaxonomy extends Model
{
    protected $table = 'term_taxonomies';

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'term_relationships', 'term_taxonomy_id', 'post_id')
            ->withTimestamps();
    }

    /**
     * The name and slug live on `terms`; term_taxonomies only points at them.
     */
    public function getNameAttribute(): ?string
    {
        return $this->term?->name;
    }

    public function getSlugAttribute(): ?string
    {
        return $this->term?->slug;
    }

    /**
     * The settings table records `with_prefix_category` for categories, which
     * is the scheme the 8000 indexed articles already link to.
     */
    public function url(): string
    {
        $prefix = $this->taxonomy === Tag::TAXONOMY ? 'tag' : 'category';

        return "/{$prefix}/{$this->slug}";
    }
}
