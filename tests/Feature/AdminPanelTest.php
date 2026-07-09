<?php

use App\Models\Permission;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
