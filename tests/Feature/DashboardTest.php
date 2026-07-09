<?php

use App\Filament\Widgets\AdminOverview;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

function dashboardAdmin(): User
{
    return User::role('superadmin')->firstOrFail();
}

it('renders the dashboard with the overview widget', function () {
    $this->actingAs(dashboardAdmin())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Посты')
        ->assertSee('Обращения');
});

it('counts the real content, skipping the orphaned taxonomy rows', function () {
    $stats = Livewire::actingAs(dashboardAdmin())->test(AdminOverview::class);

    // 7987 posts and 1 page out of the 7988 rows; 11 of the 16 category rows
    // still have a terms row behind them.
    $stats->assertSee('7 987')
        ->assertSee('11')
        ->assertSee('2 281');
});

it('shows the unread message count', function () {
    Contact::create([
        'name' => 'Тест',
        'email' => 'test@example.com',
        'message' => 'Сообщение',
    ]);

    Livewire::actingAs(dashboardAdmin())
        ->test(AdminOverview::class)
        ->assertSee('1 новых');
});

it('hides the user and role cards from a redactor', function () {
    $stats = Livewire::actingAs(User::role('redactor')->firstOrFail())->test(AdminOverview::class);

    $stats->assertSee('Посты')
        ->assertDontSee('Пользователи')
        ->assertDontSee('Права');
});
