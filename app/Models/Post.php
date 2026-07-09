<?php

namespace App\Models;

use App\Casts\Flag;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'post_title', 'post_name', 'post_content', 'post_summary', 'post_image',
    'post_status', 'post_visibility', 'comment_status', 'show_image',
    'marquee', 'ads_show', 'pr_news', 'meta_description', 'meta_keyword',
    'post_hits', 'post_author',
])]
class Post extends Model
{
    public const TYPE = 'post';

    protected $table = 'posts';

    /**
     * `meta_description` is NOT NULL without a default, and `post_guid` and
     * `post_mime_type` are NOT NULL too — an insert that omits them fails.
     */
    protected $attributes = [
        'post_status' => 'publish',
        'post_visibility' => 'public',
        'comment_status' => 'open',
        'show_image' => 'yes',
        'post_content' => '',
        'meta_description' => '',
        'post_guid' => '',
        'post_mime_type' => '',
        'post_hits' => 1,
    ];

    /**
     * Posts and pages share this table, told apart by `post_type`. Both read
     * the type through late static binding, so App\Models\Page only has to
     * redeclare the constant.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('post_type', function (Builder $query): void {
            $query->where('posts.post_type', static::TYPE);
        });

        static::creating(function (self $post): void {
            $post->post_type ??= static::TYPE;
        });
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'post_hits' => 'integer',
            'show_image' => Flag::class.':yes,no',
            'marquee' => Flag::class.':yes,no',
            // Every row in the dump is off, so the on-value is an assumption.
            'pr_news' => Flag::class.':1,0',
        ];
    }

    /**
     * The column is NOT NULL, but an empty form field arrives as null.
     */
    protected function metaDescription(): Attribute
    {
        return Attribute::make(set: fn (?string $value): string => (string) $value);
    }

    protected function postContent(): Attribute
    {
        return Attribute::make(set: fn (?string $value): string => (string) $value);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'post_author');
    }

    public function categories(): BelongsToMany
    {
        return $this->taxonomyRelation(Category::class, Category::TAXONOMY);
    }

    public function tags(): BelongsToMany
    {
        return $this->taxonomyRelation(Tag::class, Tag::TAXONOMY);
    }

    /**
     * Categories and tags share the `term_relationships` pivot. Laravel builds
     * detach queries from the pivot alone, so without this constraint syncing
     * one taxonomy would wipe the other.
     */
    protected function taxonomyRelation(string $related, string $taxonomy): BelongsToMany
    {
        return $this->belongsToMany($related, 'term_relationships', 'post_id', 'term_taxonomy_id')
            ->withTimestamps()
            ->wherePivotIn(
                'term_taxonomy_id',
                TermTaxonomy::query()->where('taxonomy', $taxonomy)->select('id'),
            );
    }

    /**
     * `post_image` holds a path relative to images/, e.g. 2026/07/abc.jpeg.
     */
    public static function imagePath(?string $image): ?string
    {
        if (blank($image)) {
            return null;
        }

        return str_starts_with($image, 'images/') ? $image : 'images/'.$image;
    }
}
