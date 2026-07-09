<?php

namespace App\Models;

use App\Casts\Flag;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'active'])]
class AdPlacement extends Model
{
    protected $table = 'ad_placements';

    protected $attributes = [
        'active' => 'y',
    ];

    protected function casts(): array
    {
        return [
            'active' => Flag::class.':y,n',
        ];
    }

    /**
     * The schema allows many banners per placement, but every placement in the
     * data holds at most one, and the old admin only ever offered one.
     */
    public function advertisements(): BelongsToMany
    {
        return $this->belongsToMany(Advertisement::class, 'ad_placement_advertisement');
    }

    public function advertisement(): ?Advertisement
    {
        return $this->advertisements->first();
    }

    /**
     * The banner a slot should show, or null when the slot is off, empty, or
     * holds a banner that has been switched off.
     */
    public static function bannerFor(string $slug): ?Advertisement
    {
        $placement = static::query()
            ->where('slug', $slug)
            ->where('active', 'y')
            ->with('advertisements')
            ->first();

        return $placement?->advertisements->firstWhere('active', true);
    }
}
