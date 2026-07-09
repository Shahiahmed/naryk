<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'url', 'icon'])]
class SocialMedia extends Model
{
    protected $table = 'socialmedia';

    /**
     * The client's dump lost the AUTO_INCREMENT on this table, so an insert
     * that leaves `id` to the database fails. Assigning it here works whether
     * or not the column carries AUTO_INCREMENT.
     */
    protected static function booted(): void
    {
        static::creating(function (self $social): void {
            $social->id ??= (static::max('id') ?? 0) + 1;
        });
    }
}
