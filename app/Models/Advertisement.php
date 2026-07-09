<?php

namespace App\Models;

use App\Casts\Flag;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'type', 'url', 'image', 'size', 'ga', 'active'])]
class Advertisement extends Model
{
    /**
     * Every row in the dump is an image banner: `ga` is NULL throughout and the
     * google_adsense table is empty, so the other two types the old admin
     * offered were never used.
     */
    protected $attributes = [
        'type' => 'image',
        'active' => 'y',
    ];

    protected function casts(): array
    {
        return [
            'active' => Flag::class.':y,n',
        ];
    }

    public function placements(): BelongsToMany
    {
        // The pivot has no timestamps.
        return $this->belongsToMany(AdPlacement::class, 'ad_placement_advertisement');
    }

    /**
     * `image` holds a bare filename; the file lives in ad/ on the public disk.
     */
    public static function imagePath(?string $image): ?string
    {
        if (blank($image)) {
            return null;
        }

        return str_contains($image, '/') ? $image : 'ad/'.$image;
    }
}
