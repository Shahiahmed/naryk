<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A key/value store: one row per setting, bucketed by `group`.
 */
#[Fillable(['group', 'name', 'value'])]
class Setting extends Model
{
    /**
     * @return array<string, array<string, string|null>>
     */
    public static function tree(): array
    {
        return static::all()
            ->groupBy('group')
            ->map(fn ($rows) => $rows->pluck('value', 'name')->all())
            ->all();
    }

    /**
     * Updates what exists and only inserts a row that carries a value. An empty
     * field on a setting the client never had must not add a row to their
     * table.
     *
     * @param  array<string, array<string, mixed>>  $tree
     */
    public static function persist(array $tree): void
    {
        foreach ($tree as $group => $values) {
            foreach ($values as $name => $value) {
                $setting = static::where('group', $group)->where('name', $name)->first();

                if (! $setting && blank($value)) {
                    continue;
                }

                static::updateOrCreate(
                    ['group' => $group, 'name' => $name],
                    ['value' => blank($value) ? null : (string) $value],
                );
            }
        }
    }

    public static function get(string $group, string $name, ?string $default = null): ?string
    {
        return static::where('group', $group)->where('name', $name)->value('value') ?? $default;
    }

    /**
     * The logo_image group holds bare filenames; the files live in assets/ on
     * the public disk.
     */
    public static function assetPath(?string $file): ?string
    {
        if (blank($file)) {
            return null;
        }

        return str_contains($file, '/') ? $file : 'assets/'.$file;
    }
}
