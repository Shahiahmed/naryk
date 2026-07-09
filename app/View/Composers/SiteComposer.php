<?php

namespace App\View\Composers;

use App\Models\Menu;
use App\Models\Setting;
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
            'socials' => array_filter($settings['social_media'] ?? []),
        ]);
    }
}
