<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Legacy boolean columns store text: `yes`/`no` for show_image and marquee,
 * `1`/`0` for pr_news. Reading tolerates every variant seen in the dump
 * (including NULL and an empty string); writing keeps the column's own words.
 *
 * @implements CastsAttributes<bool, bool>
 */
class Flag implements CastsAttributes
{
    public function __construct(
        protected string $true = 'yes',
        protected string $false = 'no',
    ) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): bool
    {
        return in_array(strtolower((string) $value), ['yes', '1', 'on', 'true'], true);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return $value ? $this->true : $this->false;
    }
}
