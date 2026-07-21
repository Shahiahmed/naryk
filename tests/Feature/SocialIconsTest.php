<?php

use App\Models\Post;
use App\Support\Icons;
use App\Support\Social;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('keeps a full url as it is', function () {
    expect(Social::url('facebook', 'https://www.facebook.com/naryk.kz'))
        ->toBe('https://www.facebook.com/naryk.kz');
});

it('turns a bare handle into a url', function () {
    expect(Social::url('twitter', 'narykkz'))->toBe('https://twitter.com/narykkz')
        ->and(Social::url('telegram', '@narykkz'))->toBe('https://t.me/narykkz');
});

it('turns a phone number into a whatsapp link', function () {
    expect(Social::url('whatsapp', '77781121332'))->toBe('https://wa.me/77781121332')
        ->and(Social::url('whatsapp', '+7 778 112 1332'))->toBe('https://wa.me/77781121332');
});

it('drops the networks the client never filled in', function () {
    // The unused rows hold NULL or a literal question mark.
    expect(Social::url('youtube', null))->toBeNull()
        ->and(Social::url('linkedin', '?'))->toBeNull()
        ->and(Social::url('facebook', '  '))->toBeNull()
        ->and(Social::url('myspace', 'anything'))->toBeNull();
});

it('shows only the five networks the client listed, in their order', function () {
    // Point 4. The old theme printed every row it found, including the empty
    // ones and networks the client no longer uses.
    $links = Social::links([
        'facebook' => 'https://www.facebook.com/naryk.kz',
        'youtube' => 'https://youtube.com/naryk',
        'linkedin' => '?',
        'whatsapp' => '77781121332',
    ]);

    expect(array_keys($links))->toBe(['telegram', 'instagram', 'tiktok', 'threads', 'facebook'])
        ->and($links['facebook'])->toBe('https://www.facebook.com/naryk.kz');
});

it('ignores a bare domain stored as a handle', function () {
    /*
     * The dump holds `naryk.kz` as the telegram handle — the site's own domain.
     * t.me/naryk.kz leads nowhere, which is why the icons opened nothing.
     */
    expect(Social::url('telegram', 'naryk.kz'))->toBeNull()
        ->and(Social::url('telegram', 'narykkz'))->toBe('https://t.me/narykkz');

    $links = Social::links(['telegram' => 'naryk.kz']);

    expect($links['telegram'])->toBe('https://t.me/narykkz');
});

it('falls back to the accounts the client gave', function () {
    // The settings have no rows at all for tiktok or threads.
    $links = Social::links([]);

    expect($links['tiktok'])->toContain('tiktok.com/@naryk.kz')
        ->and($links['threads'])->toContain('threads.com/@narykkz')
        ->and($links['instagram'])->toContain('instagram.com/narykkz');
});

it('has a glyph for every network it shows', function () {
    foreach (Icons::ORDER as $network) {
        expect(Icons::has($network))->toBeTrue();
    }

    expect(Icons::ORDER)->toBe(['telegram', 'instagram', 'tiktok', 'threads', 'facebook'])
        ->and(Icons::path('myspace'))->toBeNull();
});

it('renders the share buttons as inline svg, not letters', function () {
    $post = Post::published()->newest()->firstOrFail();

    $html = $this->get($post->url())->assertOk()->getContent();

    expect($html)->toContain('<svg class="icon');

    // Point 5: one set, under the article. The rail beside it is gone.
    expect(substr_count($html, 'share__link--telegram'))->toBe(1)
        ->and($html)->not->toContain('article-rail');
});

it('shows all five networks under an article, in their usual order', function () {
    /*
     * Point 4. Telegram and Facebook share the article; Instagram, TikTok and
     * Threads have no share endpoint, so they open the paper's own account
     * rather than pretending to.
     */
    $post = Post::published()->newest()->firstOrFail();

    $html = $this->get($post->url())->assertOk()->getContent();

    $share = str($html)->after('class="share ')->before('</div>')->toString();

    $positions = collect(Icons::ORDER)
        ->map(fn (string $network) => strpos($share, 'share__link--'.$network));

    expect($positions->every(fn ($at) => $at !== false))->toBeTrue()
        ->and($positions->toArray())->toBe($positions->sort()->values()->toArray());

    expect($share)->toContain('t.me/share/url')
        ->and($share)->toContain('facebook.com/sharer')
        ->and($share)->toContain('instagram.com/');
});

it('draws the same five icons in the header and the footer', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('socials--header')
        ->and($html)->toContain('socials--footer')
        ->and($html)->toContain('https://www.tiktok.com/@naryk.kz')
        ->and($html)->toContain('https://www.threads.com/@narykkz');

    // Point 24: no leftovers from the old theme.
    expect($html)->not->toContain('wa.me')
        ->and($html)->not->toContain('linkedin.com');
});
