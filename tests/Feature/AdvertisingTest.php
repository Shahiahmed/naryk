<?php

use App\Filament\Resources\AdPlacements\AdPlacementResource;
use App\Filament\Resources\AdPlacements\Pages\EditAdPlacement;
use App\Filament\Resources\Advertisements\Pages\CreateAdvertisement;
use App\Filament\Resources\Advertisements\Pages\EditAdvertisement;
use App\Models\AdPlacement;
use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

function adAdmin(): User
{
    return User::role('superadmin')->firstOrFail();
}

it('lists banners and placements', function () {
    $this->actingAs(adAdmin())->get('/admin/advertisements')->assertOk();
    $this->actingAs(adAdmin())->get('/admin/ad-placements')->assertOk();
});

it('reads the y/n active column as a boolean', function () {
    expect(Advertisement::find(1)->active)->toBeTrue()
        ->and(AdPlacement::find(5)->active)->toBeFalse();
});

it('writes the active column back as y or n', function () {
    $placement = AdPlacement::find(5);
    $placement->active = true;
    $placement->save();

    expect(DB::table('ad_placements')->where('id', 5)->value('active'))->toBe('y');
});

it('resolves the banner image path', function () {
    expect(Advertisement::imagePath('Qo85dU6COo.jpg'))->toBe('ad/Qo85dU6COo.jpg')
        ->and(Advertisement::imagePath('ad/Qo85dU6COo.jpg'))->toBe('ad/Qo85dU6COo.jpg')
        ->and(Advertisement::imagePath(null))->toBeNull();
});

it('splits the size string into width and height on the edit form', function () {
    Livewire::actingAs(adAdmin())
        ->test(EditAdvertisement::class, ['record' => 6])
        ->assertFormSet(['width' => '1000', 'height' => '133']);
});

it('joins width and height back into the size column', function () {
    Livewire::actingAs(adAdmin())
        ->test(EditAdvertisement::class, ['record' => 6])
        ->fillForm(['width' => 970, 'height' => 90])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Advertisement::find(6)->size)->toBe('970x90');
});

it('creates a banner as an active image', function () {
    Livewire::actingAs(adAdmin())
        ->test(CreateAdvertisement::class)
        ->fillForm([
            'name' => 'Тестовый баннер',
            'width' => 300,
            'height' => 250,
            'url' => 'https://example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $banner = Advertisement::where('name', 'Тестовый баннер')->firstOrFail();

    // `type` and `active` are NOT NULL, and the form offers neither.
    $raw = DB::table('advertisements')->where('id', $banner->id)->first();
    expect($raw->type)->toBe('image')
        ->and($raw->active)->toBe('y')
        ->and($raw->size)->toBe('300x250');
});

it('loads the banner of a placement from the pivot', function () {
    Livewire::actingAs(adAdmin())
        ->test(EditAdPlacement::class, ['record' => 6])
        ->assertFormSet(['advertisement_id' => 6]);
});

it('swaps the banner of a placement without leaving the old link behind', function () {
    Livewire::actingAs(adAdmin())
        ->test(EditAdPlacement::class, ['record' => 6])
        ->fillForm(['advertisement_id' => 1])
        ->call('save')
        ->assertHasNoFormErrors();

    $links = DB::table('ad_placement_advertisement')->where('ad_placement_id', 6)->pluck('advertisement_id');

    expect($links->all())->toBe([1]);
});

it('detaches the banner when a placement is cleared', function () {
    Livewire::actingAs(adAdmin())
        ->test(EditAdPlacement::class, ['record' => 6])
        ->fillForm(['advertisement_id' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(DB::table('ad_placement_advertisement')->where('ad_placement_id', 6)->count())->toBe(0);
});

it('does not offer to create placements', function () {
    // ad_placements.id has no AUTO_INCREMENT, so an insert would fail.
    expect(AdPlacementResource::canCreate())->toBeFalse()
        ->and(AdPlacementResource::canDeleteAny())->toBeFalse();

    $this->actingAs(adAdmin())->get('/admin/ad-placements/create')->assertNotFound();
});
