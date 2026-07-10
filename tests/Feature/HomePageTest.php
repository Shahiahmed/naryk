<?php

use App\Models\AdPlacement;
use App\Models\Post;
use App\Support\Quotes;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Cache::forget('naryk.quotes');
});

/**
 * Http::fake() merges stubs and the first match wins, so a later fake cannot
 * override an earlier one. Each test registers exactly the stub it needs.
 */
function fakeQuotes(): void
{
    Http::fake([
        'apps.naryk.kz/*' => Http::response([
            'time' => '2026.07.09 18:00',
            'kase' => [
                'KSPI' => ['last' => '41400.00', 'status' => 'DOWN'],
                'HSBK' => ['last' => '361.28', 'status' => 'UP'],
            ],
        ]),
    ]);
}

it('renders the home page', function () {
    fakeQuotes();

    $this->get('/')
        ->assertOk()
        ->assertSee('Мамандар пікірі')
        ->assertSee('Тағы да');
});

it('shows fifteen posts and a load-more button', function () {
    fakeQuotes();

    $feed = $this->get('/')->viewData('feed');

    expect($feed->count())->toBe(15)
        ->and($feed->hasMorePages())->toBeTrue();
});

it('ships the sentinel the scroll loader watches, and keeps the button as a fallback', function () {
    fakeQuotes();

    $this->get('/')
        ->assertSee('id="feed-sentinel"', escape: false)
        ->assertSee('id="load-more"', escape: false);
});

it('puts the newest post first', function () {
    fakeQuotes();

    $newest = Post::published()->newest()->first();

    $this->get('/')->assertSeeText($newest->post_title);
});

it('orders the ticker with FRHC first and skips what the endpoint omits', function () {
    fakeQuotes();

    $quotes = (new Quotes)->get();

    // The endpoint returns no FRHC yet: Freedom trades on Nasdaq, not KASE.
    expect(array_column($quotes['items'], 'ticker'))->toBe(['KSPI', 'HSBK'])
        ->and($quotes['time'])->toBe('2026.07.09 18:00');
});

it('survives the quotes endpoint being down', function () {
    Http::fake(['apps.naryk.kz/*' => Http::response(null, 500)]);

    expect((new Quotes)->get()['items'])->toBe([]);

    $this->get('/')->assertOk();
});

it('serves the next slice of the feed without a banner', function () {
    $response = $this->get('/feed?page=2')->assertOk();

    expect($response->viewData('feedBanner'))->toBeNull()
        ->and($response->viewData('feed')->currentPage())->toBe(2);
});

it('returns nothing once the feed runs out', function () {
    $lastPage = (int) ceil(Post::published()->count() / config('naryk.feed.per_page'));

    $body = trim($this->get('/feed?page='.($lastPage + 1))->getContent());

    expect($body)->toBe('');
});

it('renders the in-feed banner only when the placement is on', function () {
    fakeQuotes();

    // home-horizontal ships switched off.
    expect(AdPlacement::bannerFor('home-horizontal'))->toBeNull();

    AdPlacement::where('slug', 'home-horizontal')->update(['active' => 'y']);

    $banner = AdPlacement::bannerFor('home-horizontal');

    expect($banner)->not->toBeNull()
        ->and($banner->active)->toBeTrue();

    $this->get('/')->assertSee($banner->imageUrl(), escape: false);
});

it('hides a banner whose placement points at a disabled ad', function () {
    AdPlacement::where('slug', 'home-horizontal')->update(['active' => 'y']);
    $banner = AdPlacement::bannerFor('home-horizontal');
    $banner->update(['active' => false]);

    expect(AdPlacement::bannerFor('home-horizontal'))->toBeNull();
});

it('leaves the special projects column out until the category exists', function () {
    fakeQuotes();

    $response = $this->get('/');

    expect($response->viewData('specialProjects'))->toBeEmpty();

    $response->assertDontSee('Арнайы жобалар');
});

/**
 * A path whose file really is in storage — most of the newest posts point at
 * images the client's archive never shipped.
 */
function anExistingImage(): string
{
    $files = Storage::disk('public')->files('images/2026/06');

    expect($files)->not->toBeEmpty();

    return Str::after($files[0], 'images/');
}

it('renders a tall card with the title on the image and no lead', function () {
    fakeQuotes();

    $post = Post::published()->newest()->firstOrFail();
    $post->update([
        'show_image' => Post::LAYOUT_TALL,
        'post_image' => anExistingImage(),
        'post_summary' => '<p>Лид, который не должен появиться.</p>',
    ]);

    $html = $this->get('/')->getContent();

    expect($html)->toContain('card--tall')
        ->and($html)->toContain('card__title--inverse')
        ->and($html)->not->toContain('Лид, который не должен появиться');
});

it('renders a text-only card with a lead and no image', function () {
    fakeQuotes();

    $image = anExistingImage();
    $post = Post::published()->newest()->firstOrFail();
    $post->update([
        'show_image' => Post::LAYOUT_TEXT,
        'post_image' => $image,
        'post_summary' => '<p>Тек лид қана.</p>',
    ]);

    $this->get('/')
        ->assertSeeText('Тек лид қана.')
        ->assertDontSee($image, escape: false);
});

it('falls back to a text card when the image file is missing', function () {
    fakeQuotes();

    $post = Post::published()->newest()->firstOrFail();
    $post->update([
        'show_image' => Post::LAYOUT_TALL,
        'post_image' => '2026/07/does-not-exist.jpeg',
    ]);

    expect($post->hasImage())->toBeFalse();

    $this->get('/')->assertDontSee('does-not-exist.jpeg', escape: false);
});

it('marks a PR post with a badge', function () {
    fakeQuotes();

    $post = Post::published()->newest()->firstOrFail();
    $post->pr_news = true;
    $post->save();

    $this->get('/')->assertSee('pr-badge');
});

it('strips the html the editors leave in a lead', function () {
    $post = new Post(['post_summary' => '<p>Бірінші сөйлем.&nbsp;</p>']);

    expect($post->lead())->toBe('Бірінші сөйлем.');

    expect((new Post(['post_summary' => '<p><br></p>']))->lead())->toBeNull();
});

it('builds the post url from the permalink setting', function () {
    $post = Post::published()->newest()->first();

    expect($post->url())->toBe('/news/'.$post->post_name);
});
