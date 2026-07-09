<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * The legacy `permissions` table carries an extra `alias` column that is
 * NOT NULL without a default. Spatie knows nothing about it, so fill it here
 * rather than altering the client's schema.
 */
class Permission extends SpatiePermission
{
    protected static function booted(): void
    {
        static::creating(function (self $permission): void {
            $permission->alias ??= $permission->name;
        });
    }
}
