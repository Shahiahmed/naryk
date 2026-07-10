<?php

namespace App\Models;

use App\Casts\Flag;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'post_title', 'post_name', 'post_content', 'post_summary', 'post_image',
    'post_status', 'post_visibility', 'comment_status', 'show_image',
    'marquee', 'ads_show', 'pr_news', 'meta_description', 'meta_keyword',
    'post_hits', 'post_author',
])]
class Post extends Model
{
    public const TYPE = 'post';

    /**
     * How the feed renders a post. `show_image` is a varchar, not an enum, so
     * the third layout fits the client's schema untouched. Legacy rows carry
     * `yes`, which has always meant a wide image above the title.
     */
    public const LAYOUT_WIDE = 'yes';

    public const LAYOUT_TALL = 'vertical';

    public const LAYOUT_TEXT = 'no';

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
            'marquee' => Flag::class.':yes,no',
            // Every row in the dump is off, so the on-value is an assumption.
            'pr_news' => Flag::class.':1,0',
        ];
    }

    /**
     * One row stores `public` and one stores NULL — neither is a layout, and
     * both should fall back to the wide image the site has always shown.
     */
    public function layout(): string
    {
        return match ($this->show_image) {
            self::LAYOUT_TALL => self::LAYOUT_TALL,
            self::LAYOUT_TEXT => self::LAYOUT_TEXT,
            default => self::LAYOUT_WIDE,
        };
    }

    /**
     * A handful of rows point at files the client's storage archive never had.
     * Rather than render a broken image, such a post falls back to the
     * text-only card.
     */
    public function hasImage(): bool
    {
        if ($this->layout() === self::LAYOUT_TEXT || blank($this->post_image)) {
            return false;
        }

        return Storage::disk('public')->exists(self::imagePath($this->post_image));
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
     * @param  Builder<self>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('post_status', 'publish')->where('post_visibility', 'public');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeInCategory(Builder $query, string $slug): void
    {
        $query->whereHas(
            'categories.term',
            fn (Builder $term): Builder => $term->where('slug', $slug),
        );
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeInTag(Builder $query, string $slug): void
    {
        $query->whereHas(
            'tags.term',
            fn (Builder $term): Builder => $term->where('slug', $slug),
        );
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeNewest(Builder $query): void
    {
        $query->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * Bump the view counter without touching `updated_at` — the column drives
     * the sitemap, and a read should not reorder the feed.
     */
    public function recordHit(): void
    {
        static::withoutTimestamps(fn () => $this->increment('post_hits'));
    }

    /**
     * `ads_show` picks the banner slot inside an article: `hide` shows none,
     * `iworld` swaps in the Its-World slot, anything else takes the default.
     */
    public function bannerPlacement(): ?string
    {
        return match ($this->ads_show) {
            'hide' => null,
            'iworld' => 'its-world',
            default => 'single-720',
        };
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

    public function imageUrl(): ?string
    {
        $path = self::imagePath($this->post_image);

        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function url(): string
    {
        return '/'.trim((string) config('naryk.post_prefix'), '/').'/'.$this->post_name;
    }

    /**
     * The lead is one sentence: the editors write it in `post_summary`, wrapped
     * in the HTML their old rich editor produced.
     */
    public function lead(): ?string
    {
        $text = trim(html_entity_decode(strip_tags((string) $this->post_summary), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        return $text !== '' ? $text : null;
    }
}
