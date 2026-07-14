<?php

use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\SocialMedia\Pages\CreateSocialMedia;
use App\Models\Setting;
use App\Models\SocialMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

function settingsAdmin(): User
{
    return User::role('superadmin')->firstOrFail();
}

it('opens the settings page and the social media list', function () {
    $this->actingAs(settingsAdmin())->get('/admin/manage-settings')->assertOk();
    $this->actingAs(settingsAdmin())->get('/admin/social-media')->assertOk();
});

it('hides the settings page from a redactor', function () {
    $this->actingAs(User::role('redactor')->firstOrFail())
        ->get('/admin/manage-settings')
        ->assertForbidden();
});

it('groups the settings by their group column', function () {
    $tree = Setting::tree();

    expect($tree['site_information']['company_name'])->toBe('Naryk.kz')
        ->and($tree['google']['googleanalyticsid'])->toBe('G-52DGC6SC0C')
        ->and($tree['permalinks']['permalink'])->toBe('news')
        ->and($tree['category_permalinks']['category_permalink_type'])->toBe('with_prefix_category');
});

it('fills the form from the settings table', function () {
    $page = Livewire::actingAs(settingsAdmin())
        ->test(ManageSettings::class)
        ->assertSet('data.site_information.company_name', 'Naryk.kz')
        ->assertSet('data.permalinks.permalink_type', 'custom');

    // FileUpload keys its state by a generated uuid.
    expect(array_values($page->get('data')['logo_image']['logowebsite']))->toBe(['assets/logo.svg']);
});

it('reads the maintenance flag as a boolean toggle', function () {
    Livewire::actingAs(settingsAdmin())
        ->test(ManageSettings::class)
        ->assertSet('data.site_config.maintenance', false);
});

it('writes the maintenance toggle back as y or n', function () {
    Livewire::actingAs(settingsAdmin())
        ->test(ManageSettings::class)
        ->fillForm(['site_config' => ['maintenance' => true]])
        ->call('save');

    expect(Setting::get('site_config', 'maintenance'))->toBe('y');
});

it('saves a changed setting without touching the others', function () {
    $before = DB::table('settings')->count();

    Livewire::actingAs(settingsAdmin())
        ->test(ManageSettings::class)
        ->fillForm(['site_information' => ['company_name' => 'Изменено']])
        ->call('save');

    expect(Setting::get('site_information', 'company_name'))->toBe('Изменено')
        ->and(Setting::get('site_information', 'siteurl'))->toBe('https://naryk.kz')
        ->and(DB::table('settings')->count())->toBe($before);
});

it('does not add a row for a setting left empty', function () {
    $before = DB::table('settings')->count();

    // The sponsor group has no rows in the client's dump. Opening the page and
    // saving it untouched must not create them.
    Livewire::actingAs(settingsAdmin())
        ->test(ManageSettings::class)
        ->call('save');

    expect(DB::table('settings')->count())->toBe($before)
        ->and(Setting::get('sponsor', 'logo'))->toBeNull();
});

it('resolves the logo path into the assets directory', function () {
    expect(Setting::assetPath('logo.svg'))->toBe('assets/logo.svg')
        ->and(Setting::assetPath('assets/logo.svg'))->toBe('assets/logo.svg')
        ->and(Setting::assetPath(null))->toBeNull();
});

it('creates a social media row despite the missing AUTO_INCREMENT', function () {
    expect(DB::selectOne(
        "SELECT AUTO_INCREMENT ai FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'socialmedia'"
    )->ai)->toBeNull();

    Livewire::actingAs(settingsAdmin())
        ->test(CreateSocialMedia::class)
        ->fillForm([
            'name' => 'Telegram',
            'slug' => 'telegram-test',
            'url' => 'https://t.me/naryk',
            'icon' => 'fab fa-telegram',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $created = SocialMedia::where('slug', 'telegram-test')->firstOrFail();

    expect($created->id)->toBe(6);
});
