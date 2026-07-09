<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Legacy boolean columns each spell truth their own way: `yes`/`no` for
 * show_image and marquee, `1`/`0` for pr_news, `y`/`n` for the ad tables.
 * A value only counts as true when it matches that column's own word, so the
 * stray NULLs and empty strings in the dump all read as false.
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
        return strtolower((string) $value) === strtolower($this->true);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return $value ? $this->true : $this->false;
    }
}
