<?php

namespace App\View\Composers;

use App\Models\Menu;
use App\Models\Setting;
use App\Support\Social;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteComposer
{
    public function compose(View $view): void
    {
        $settings = Setting::tree();

        $view->with([
            'settings' => $settings,
            'headerMenu' => Menu::with('items')->where('name', 'header')->first(),
            'footerMenu' => Menu::with('items')->where('name', 'footer')->first(),
            'logo' => Setting::assetPath($settings['logo_image']['logowebsite'] ?? null),
            'logoFooter' => Setting::assetPath($settings['logo_image']['logowebsite_footer'] ?? null),
            'favicon' => Setting::assetPath($settings['logo_image']['favicon'] ?? null),
            'socials' => Social::links($settings['social_media'] ?? []),
            'sponsor' => $this->sponsor($settings['sponsor'] ?? []),
        ]);
    }

    /**
     * The sponsor block in the navigation. Editable in the admin panel, but it
     * falls back to the Freedom Broker logo the old site shipped, so a fresh
     * install looks right before anyone touches the settings.
     *
     * @param  array<string, string|null>  $sponsor
     * @return array{logo: string, url: ?string, title: string}|null
     */
    protected function sponsor(array $sponsor): ?array
    {
        $logo = filled($sponsor['logo'] ?? null)
            ? Storage::disk('public')->url(Setting::assetPath($sponsor['logo']))
            : asset('img/broker.svg');

        return [
            'logo' => $logo,
            'url' => filled($sponsor['url'] ?? null)
                ? $sponsor['url']
                : 'https://fbroker.kz/?utm_source=naryk.kz&utm_medium=banner&utm_campaign=PR_2025',
            'title' => filled($sponsor['title'] ?? null) ? $sponsor['title'] : 'Freedom Broker',
        ];
    }
}
