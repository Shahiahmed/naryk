<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * `parent`, `depth` and `role_id` are NOT NULL with a default of 0. Every row
 * in the dump is a flat, public link, and the old admin offered no nesting, so
 * the form leaves all three to the database.
 */
#[Fillable(['label', 'link', 'class', 'sort'])]
class MenuItem extends Model
{
    protected $table = 'menu_items';

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    /**
     * Not named `menu()`: the foreign key column already owns that name, and an
     * attribute shadows a relation of the same name.
     */
    public function parentMenu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu');
    }
}
