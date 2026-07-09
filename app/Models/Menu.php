<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Menu extends Model
{
    public function items(): HasMany
    {
        // The foreign key column is called `menu`, not `menu_id`.
        return $this->hasMany(MenuItem::class, 'menu')->orderBy('sort');
    }
}
