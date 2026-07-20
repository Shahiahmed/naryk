<?php

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Permission;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

it('shows the login page to guests', function () {
    $this->get('/admin/login')->assertOk();
});

it('authenticates a superadmin through the login form', function () {
    $superadmin = User::role('superadmin')->firstOrFail();
    $superadmin->password = 'secret-for-test';
    $superadmin->save();

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $superadmin->email,
            'password' => 'secret-for-test',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth()->id())->toBe($superadmin->id);
});

it('rejects a wrong password', function () {
    $superadmin = User::role('superadmin')->firstOrFail();

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $superadmin->email,
            'password' => 'definitely-not-the-password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    expect(auth()->check())->toBeFalse();
});

it('redirects guests away from the users list', function () {
    $this->get('/admin/users')->assertRedirect('/admin/login');
});

it('lets a superadmin open the users and roles lists', function () {
    $superadmin = User::role('superadmin')->firstOrFail();

    $this->actingAs($superadmin)->get('/admin/users')->assertOk();
    $this->actingAs($superadmin)->get('/admin/roles')->assertOk();
});

it('forbids a redactor from reaching users, including by direct URL', function () {
    $redactor = User::role('redactor')->firstOrFail();
    $target = User::role('superadmin')->firstOrFail();

    $this->actingAs($redactor)->get('/admin/users')->assertForbidden();
    $this->actingAs($redactor)->get("/admin/users/{$target->id}/edit")->assertForbidden();
});

it('keeps a redactor inside the panel', function () {
    $redactor = User::role('redactor')->firstOrFail();

    $this->actingAs($redactor)->get('/admin')->assertOk();
});

it('locks a deactivated user out of the panel', function () {
    $user = User::role('superadmin')->firstOrFail();
    $user->update(['status' => 'deactive']);

    expect($user->canAccessPanel(Filament\Facades\Filament::getPanel('admin')))->toBeFalse();
});

it('locks a banned user out of the panel', function () {
    $user = User::role('superadmin')->firstOrFail();

    // `banned_at` is deliberately not fillable, so assign it directly.
    $user->banned_at = now();
    $user->save();

    expect($user->canAccessPanel(Filament\Facades\Filament::getPanel('admin')))->toBeFalse();
});

it('fills the legacy alias column when creating a permission', function () {
    $permission = Permission::create(['name' => 'read-widgets', 'guard_name' => 'web']);

    expect($permission->fresh()->alias)->toBe('read-widgets');
});

it('resolves a bare photo filename into the avatar directory', function () {
    expect(User::photoPath('DJM02813.jpg'))->toBe('avatar/DJM02813.jpg')
        ->and(User::photoPath('avatar/DJM02813.jpg'))->toBe('avatar/DJM02813.jpg')
        ->and(User::photoPath(null))->toBeNull()
        ->and(User::photoPath(''))->toBeNull();
});

it('renders the avatar from the avatar directory in the users table', function () {
    $superadmin = User::role('superadmin')->firstOrFail();
    $withPhoto = User::where('photo', 'DJM02813.jpg')->firstOrFail();

    expect(Storage::disk('public')->exists('avatar/'.$withPhoto->photo))->toBeTrue();

    $this->actingAs($superadmin)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('/storage/avatar/DJM02813.jpg', escape: false);
});

it('keeps the bare filename in the column when saving the edit form', function () {
    $superadmin = User::role('superadmin')->firstOrFail();
    $target = User::where('photo', 'DJM02813.jpg')->firstOrFail();

    Livewire::actingAs($superadmin)
        ->test(EditUser::class, ['record' => $target->getKey()])
        ->fillForm(['occupation' => 'Тестовая должность'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($target->fresh()->photo)->toBe('DJM02813.jpg');
});

it('puts Контент first in the sidebar, right under the dashboard', function () {
    /*
     * The client lives in Контент and asked for it at the top. Filament renders
     * ungrouped items above every group, so Пользователи and Роли had to become
     * a group of their own — no ordering alone could have moved Контент past
     * them. This pins the order the client asked for.
     */
    $superadmin = User::role('superadmin')->firstOrFail();

    $html = $this->actingAs($superadmin)->get('/admin')->assertOk()->getContent();

    // The label sits on its own line inside the group's button, not tight
    // against the tags, so match the word itself.
    $at = function (string $group) use ($html) {
        expect($html)->toContain($group);

        return strpos($html, $group);
    };

    expect($at('Контент'))->toBeLessThan($at('Реклама'))
        ->and($at('Реклама'))->toBeLessThan($at('Настройки'))
        ->and($at('Настройки'))->toBeLessThan($at('Доступ'));
});
