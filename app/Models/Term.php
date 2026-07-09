<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name', 'slug'])]
class Term extends Model
{
    protected $table = 'terms';

    public function taxonomy(): HasOne
    {
        return $this->hasOne(TermTaxonomy::class, 'term_id');
    }
}
