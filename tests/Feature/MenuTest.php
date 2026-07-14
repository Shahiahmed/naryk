<?php

use App\Filament\Resources\Menus\Pages\EditMenu;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

function menuAdmin(): User
{
    return User::role('superadmin')->firstOrFail();
}

it('lists the menus', function () {
    $this->actingAs(menuAdmin())->get('/admin/menus')->assertOk();
});

it('hides menus from a redactor', function () {
    $this->actingAs(User::role('redactor')->firstOrFail())
        ->get('/admin/menus')
        ->assertForbidden();
});

it('loads the items of a menu in their stored order', function () {
    $header = Menu::where('name', 'header')->firstOrFail();

    // Editors add and reorder items, so assert the ordering rather than a
    // fixed list of labels.
    $sorts = $header->items->pluck('sort')->all();

    expect($sorts)->toBe(collect($sorts)->sort()->values()->all())
        ->and($header->items)->not->toBeEmpty();

    expect($header->items->pluck('label'))->toContain('Қаржы', 'Бизнес');
});

it('does not shadow the menu relation with the menu column', function () {
    $item = MenuItem::firstOrFail();

    // `menu` is the foreign key column, the relation is parentMenu().
    expect($item->menu)->toBeInt()
        ->and($item->parentMenu->name)->toBeIn(['header', 'footer']);
});

it('adds an item to a menu through the form', function () {
    $footer = Menu::where('name', 'footer')->firstOrFail();
    $items = $footer->items->map(fn (MenuItem $item): array => [
        'label' => $item->label,
        'link' => $item->link,
        'class' => $item->class,
    ])->all();

    $items[] = ['label' => 'О нас', 'link' => '/about', 'class' => null];

    Livewire::actingAs(menuAdmin())
        ->test(EditMenu::class, ['record' => $footer->getKey()])
        ->fillForm(['items' => $items])
        ->call('save')
        ->assertHasNoFormErrors();

    $footer->load('items');

    expect($footer->items->pluck('label')->all())->toBe(['Home', 'Contact', 'О нас']);

    // parent, depth and role_id are NOT NULL; the form never sends them.
    $added = DB::table('menu_items')->where('label', 'О нас')->first();
    expect($added->menu)->toBe((int) $footer->id)
        ->and($added->parent)->toBe(0)
        ->and($added->depth)->toBe(0)
        ->and($added->role_id)->toBe(0);
});

it('renumbers sort when the items are reordered', function () {
    $footer = Menu::where('name', 'footer')->firstOrFail();

    Livewire::actingAs(menuAdmin())
        ->test(EditMenu::class, ['record' => $footer->getKey()])
        ->fillForm(['items' => [
            ['label' => 'Contact', 'link' => '/contact', 'class' => null],
            ['label' => 'Home', 'link' => '/', 'class' => null],
        ]])
        ->call('save')
        ->assertHasNoFormErrors();

    $footer->load('items');

    expect($footer->items->pluck('label')->all())->toBe(['Contact', 'Home'])
        ->and($footer->items->pluck('sort')->all())->toBe([1, 2]);
});
