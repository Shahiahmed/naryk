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
    expect(Social::url('twitter', 'naryk.kz'))->toBe('https://twitter.com/naryk.kz')
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

it('builds only the links that resolve', function () {
    $links = Social::links([
        'facebook' => 'https://www.facebook.com/naryk.kz',
        'youtube' => null,
        'linkedin' => '?',
        'whatsapp' => '77781121332',
    ]);

    expect($links)->toBe([
        'facebook' => 'https://www.facebook.com/naryk.kz',
        'whatsapp' => 'https://wa.me/77781121332',
    ]);
});

it('has a glyph for every network the settings can hold', function () {
    foreach (['facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'telegram', 'whatsapp'] as $network) {
        expect(Icons::has($network))->toBeTrue();
    }

    expect(Icons::path('myspace'))->toBeNull();
});

it('renders the share buttons as inline svg, not letters', function () {
    $post = Post::published()->newest()->firstOrFail();

    $html = $this->get($post->url())->assertOk()->getContent();

    expect($html)->toContain('<svg class="icon')
        ->and(substr_count($html, '<svg class="icon'))->toBeGreaterThanOrEqual(8);

    // Two rails of four buttons each. The footer carries its own Telegram
    // icon, so count the share buttons rather than the label.
    expect(substr_count($html, 'share__link--telegram'))->toBe(2);
});

it('draws the footer icons from the settings', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('social-link')
        ->and($html)->toContain('https://wa.me/77781121332');
});
