<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Contacts\ContactResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Role;

class AdminOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $stats = [
            self::stat('Посты', Post::count(), 'heroicon-o-newspaper', 'info', PostResource::getUrl()),
            self::stat('Страницы', Page::count(), 'heroicon-o-document-text', 'danger', PageResource::getUrl()),
            self::stat('Категории', Category::count(), 'heroicon-o-folder', 'success', CategoryResource::getUrl()),
            self::stat('Теги', Tag::count(), 'heroicon-o-hashtag', 'warning', TagResource::getUrl()),
        ];

        $unread = Contact::where('status', 'unread')->count();

        $stats[] = Stat::make('Обращения', Contact::count())
            ->description($unread > 0 ? "{$unread} новых" : 'новых нет')
            ->descriptionIcon('heroicon-o-envelope')
            ->color($unread > 0 ? 'warning' : 'gray')
            ->url(ContactResource::getUrl());

        // Users, roles and permissions are only visible to admins anyway.
        if (Filament::auth()->user()?->hasAnyRole(['superadmin', 'admin'])) {
            $stats[] = self::stat('Пользователи', User::count(), 'heroicon-o-users', 'primary', UserResource::getUrl());
            $stats[] = self::stat('Роли', Role::count(), 'heroicon-o-shield-check', 'primary', RoleResource::getUrl());
            $stats[] = self::stat('Права', Permission::count(), 'heroicon-o-key', 'gray');
        }

        return $stats;
    }

    protected static function stat(string $label, int $value, string $icon, string $color, ?string $url = null): Stat
    {
        $stat = Stat::make($label, number_format($value, 0, ',', ' '))
            ->descriptionIcon($icon)
            ->color($color);

        return $url ? $stat->url($url) : $stat;
    }
}
