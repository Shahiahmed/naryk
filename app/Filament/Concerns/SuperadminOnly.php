<?php

namespace App\Filament\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

/**
 * Sections the client wants reserved for the site's owner: accounts, roles,
 * settings, the menu. An `admin` used to reach these too.
 *
 * Filament authorises each page separately, so hiding the navigation item is
 * not enough on its own — /admin/users/2/edit would still answer to anyone who
 * typed it. Every gate below is closed, not just the one the sidebar reads.
 */
trait SuperadminOnly
{
    protected static function isSuperadmin(): bool
    {
        return Filament::auth()->user()?->hasRole('superadmin') ?? false;
    }

    public static function canAccess(): bool
    {
        return static::isSuperadmin();
    }

    public static function canViewAny(): bool
    {
        return static::isSuperadmin();
    }

    public static function canCreate(): bool
    {
        return static::isSuperadmin();
    }

    public static function canEdit(Model $record): bool
    {
        return static::isSuperadmin();
    }

    public static function canDelete(Model $record): bool
    {
        return static::isSuperadmin();
    }

    public static function canDeleteAny(): bool
    {
        return static::isSuperadmin();
    }
}
