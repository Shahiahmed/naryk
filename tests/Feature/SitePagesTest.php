<?php

use App\Models\Contact;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;

uses(DatabaseTransactions::class);

it('serves a page from the site root', function () {
    $page = Page::published()->firstOrFail();

    expect($page->post_name)->toBe('about');

    $this->get('/about')
        ->assertOk()
        ->assertSee($page->post_title, escape: false);
});

it('does not serve a post through the page route', function () {
    $post = Post::published()->newest()->firstOrFail();

    $this->get('/'.$post->post_name)->assertNotFound();
});

it('404s on an unknown page', function () {
    $this->get('/joq-bet')->assertNotFound();
});

it('does not let the page route swallow the admin panel', function () {
    // /{slug} is registered last, but it must also refuse these prefixes.
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/login')->assertOk();
});

it('does not let the page route swallow the other site routes', function () {
    $this->get('/search')->assertOk();
    $this->get('/contact')->assertOk();
});

it('renders the branded 404 page', function () {
    $this->get('/joq-bet')
        ->assertNotFound()
        ->assertSee('Бет табылмады');
});

it('asks for a query before searching', function () {
    $response = $this->get('/search')->assertOk();

    expect($response->viewData('posts'))->toBeNull();
});

it('finds posts by title', function () {
    $post = Post::published()->newest()->firstOrFail();
    $word = Str::of($post->post_title)->explode(' ')->first();

    $posts = $this->get('/search?q='.urlencode($word))->assertOk()->viewData('posts');

    expect($posts->total())->toBeGreaterThan(0);
});

it('reports an empty search plainly', function () {
    $this->get('/search?q='.urlencode('zzz-eshtene-joq-zzz'))
        ->assertOk()
        ->assertSee('ештеңе табылмады');
});

it('treats a percent sign in the query as text, not a wildcard', function () {
    $posts = $this->get('/search?q=%25')->assertOk()->viewData('posts');

    // A bare `%` would otherwise match every post.
    expect($posts->total())->toBeLessThan(Post::published()->count());
});

it('stores a contact message', function () {
    $before = Contact::count();

    $this->post('/contact', [
        'name' => 'Тест',
        'email' => 'test@example.com',
        'subject' => 'Сұрақ',
        'message' => 'Хабарлама мәтіні',
    ])->assertRedirect()->assertSessionHas('sent');

    expect(Contact::count())->toBe($before + 1);

    $contact = Contact::latest('id')->first();

    expect($contact->name)->toBe('Тест')
        ->and($contact->status)->toBe('unread');
});

it('rejects an invalid contact message', function () {
    $before = Contact::count();

    $this->post('/contact', ['name' => '', 'email' => 'not-an-email', 'message' => ''])
        ->assertSessionHasErrors(['name', 'email', 'message']);

    expect(Contact::count())->toBe($before);
});

it('swallows a message that trips the honeypot', function () {
    $before = Contact::count();

    $this->post('/contact', [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'message' => 'spam',
        'website' => 'https://spam.example',
    ])->assertRedirect()->assertSessionHas('sent');

    // The bot is told it worked; nothing is written.
    expect(Contact::count())->toBe($before);
});
