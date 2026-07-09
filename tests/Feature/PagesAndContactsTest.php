<?php

use App\Filament\Resources\Contacts\ContactResource;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

function contentAdmin(): User
{
    return User::role('superadmin')->firstOrFail();
}

it('lists pages and contacts', function () {
    $this->actingAs(contentAdmin())->get('/admin/pages')->assertOk();
    $this->actingAs(contentAdmin())->get('/admin/contacts')->assertOk();
});

it('keeps posts and pages apart although they share one table', function () {
    expect(Page::count())->toBe(1)
        ->and(Post::count())->toBe(7987)
        ->and(DB::table('posts')->count())->toBe(7988);

    $about = Page::firstOrFail();

    expect($about->post_name)->toBe('about')
        ->and($about->post_type)->toBe('page')
        ->and(Post::find($about->id))->toBeNull();
});

it('creates a page with post_type page', function () {
    $author = contentAdmin();
    $slug = 'testovaya-stranica-'.uniqid();

    Livewire::actingAs($author)
        ->test(CreatePage::class)
        ->fillForm([
            'post_title' => 'Тестовая страница',
            'post_name' => $slug,
            'post_content' => '<p>Текст</p>',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::where('post_name', $slug)->firstOrFail();

    expect($page->post_author)->toBe($author->id)
        ->and(Post::where('post_name', $slug)->exists())->toBeFalse();

    $raw = DB::table('posts')->where('id', $page->id)->first();
    expect($raw->post_type)->toBe('page')
        ->and($raw->meta_description)->toBe('');
});

it('does not let anyone write a contact message from the admin', function () {
    expect(ContactResource::canCreate())->toBeFalse();

    $this->actingAs(contentAdmin())->get('/admin/contacts/create')->assertNotFound();
});

it('marks a message as read once it is opened', function () {
    $contact = Contact::create([
        'name' => 'Тест',
        'email' => 'test@example.com',
        'subject' => 'Вопрос',
        'message' => 'Текст сообщения',
    ]);

    expect($contact->status)->toBe('unread')
        ->and(ContactResource::getNavigationBadge())->toBe('1');

    $contact->markAsRead();

    expect($contact->fresh()->status)->toBe('read')
        ->and(ContactResource::getNavigationBadge())->toBeNull();
});
