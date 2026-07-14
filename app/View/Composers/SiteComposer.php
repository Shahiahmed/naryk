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

        /*
         * Point 25: the footer address is press.naryk@gmail.com. The settings
         * row still holds info@naryk.kz, so override it here rather than write
         * to the client's table; the admin panel can still change it.
         */
        $settings['site_information']['siteemail'] = filled($settings['site_information']['siteemail'] ?? null)
            && $settings['site_information']['siteemail'] !== 'info@naryk.kz'
                ? $settings['site_information']['siteemail']
                : 'press.naryk@gmail.com';

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
     * The sponsor block. Editable in the admin panel, but it falls back to the
     * Freedom Broker artwork the old site shipped, so a fresh install looks
     * right before anyone touches the settings.
     *
     * Two shapes: the wide lockup with the words for the navigation strip, and
     * the bare shield for the phone masthead, where the wide one will not fit.
     * A logo uploaded in the admin panel replaces both.
     *
     * @param  array<string, string|null>  $sponsor
     * @return array{logo: string, wide: string, url: string, title: string}
     */
    protected function sponsor(array $sponsor): array
    {
        $uploaded = filled($sponsor['logo'] ?? null)
            ? Storage::disk('public')->url(Setting::assetPath($sponsor['logo']))
            : null;

        return [
            'logo' => $uploaded ?? asset('img/broker.svg'),
            'wide' => $uploaded ?? asset('img/broker-wide.svg'),
            'url' => filled($sponsor['url'] ?? null)
                ? $sponsor['url']
                : 'https://fbroker.kz/?utm_source=naryk.kz&utm_medium=banner&utm_campaign=PR_2025',
            'title' => filled($sponsor['title'] ?? null) ? $sponsor['title'] : 'Freedom Broker',
        ];
    }
}
