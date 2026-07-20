<?php

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('ships the burger button and the menu it controls', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('id="burger"')
        ->and($html)->toContain('id="site-menu"')
        ->and($html)->toContain('aria-controls="site-menu"')
        ->and($html)->toContain('aria-expanded="false"');
});

it('keeps the menu links in the markup, so it works without javascript', function () {
    $this->get('/about')
        ->assertOk()
        ->assertSee('/category/qarzhy', escape: false)
        ->assertSee('Қаржы', escape: false);
});

it('falls back to the freedom broker artwork before anyone sets a sponsor', function () {
    expect(Setting::get('sponsor', 'logo'))->toBeNull();

    $sponsor = $this->get('/about')->viewData('sponsor');

    expect($sponsor['logo'])->toEndWith('/img/broker.svg')
        ->and($sponsor['wide'])->toEndWith('/img/broker-wide.svg')
        ->and($sponsor['url'])->toContain('fbroker.kz')
        ->and($sponsor['title'])->toBe('Freedom Broker');
});

it('marks the sponsor link as an ad and opens it in a new tab', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('rel="noopener sponsored"');
});

it('renders both sponsor shapes, and hides each where the other belongs', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    // The wide lockup rides in the nav strip, the shield in the phone
    // masthead. CSS shows one or the other; both ship in the markup.
    expect($html)->toContain('sponsor--wide')
        ->and($html)->toContain('sponsor--compact')
        ->and($html)->toContain('img/broker-wide.svg')
        ->and($html)->toContain('img/broker.svg');
});

it('orders the masthead: freedom, search, wordmark, socials, about', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    // Point 7.
    $sponsor = strpos($html, 'sponsor--wide');
    $search = strpos($html, 'site-search__input');
    $wordmark = strpos($html, 'site-header__logo');
    $socials = strpos($html, 'socials--header');
    $about = strpos($html, 'site-header__about');

    expect($sponsor)->toBeLessThan($search)
        ->and($search)->toBeLessThan($wordmark)
        ->and($wordmark)->toBeLessThan($socials)
        ->and($socials)->toBeLessThan($about);
});

it('keeps the about link out of the rubric strip, since it sits in the masthead', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    $strip = str($html)->after('site-nav__inner')->before('</nav>')->toString();

    expect($strip)->not->toContain('/about')
        ->and($strip)->toContain('/category/');
});

it('lets the admin panel replace both sponsor shapes at once', function () {
    Setting::persist([
        'sponsor' => [
            'logo' => 'logo.svg',
            'url' => 'https://example.com/?utm_source=naryk',
            'title' => 'Демо-спонсор',
        ],
    ]);

    $sponsor = $this->get('/about')->viewData('sponsor');

    expect($sponsor['logo'])->toContain('assets/logo.svg')
        ->and($sponsor['wide'])->toContain('assets/logo.svg')
        ->and($sponsor['url'])->toBe('https://example.com/?utm_source=naryk')
        ->and($sponsor['title'])->toBe('Демо-спонсор');
});

it('scales the wide sponsor logo, rather than pinning it to a 1920x1080 canvas', function () {
    $svg = file_get_contents(public_path('img/broker-wide.svg'));

    // The file the old site shipped declared the whole 1920x1080 artboard, so
    // the logo drew tiny in one corner.
    expect($svg)->not->toContain('viewBox="0 0 1920 1080"')
        ->and($svg)->toContain('viewBox="261 318 1432 449"');
});

it('uses the wordmark the client supplied', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('img/logo-desktop.png');
});

it('shows one wordmark everywhere, phone included', function () {
    /*
     * The masthead used to swap in a two-line phone variant below 800px. The
     * brief's point 14 asks for НАРЫҚ ЖАҢАЛЫҚТАРЫ on the phone too, so the
     * swap is gone and one file serves both.
     */
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('img/logo-desktop.png')
        ->and($html)->not->toContain('logo-phone.png')
        ->and($html)->not->toContain('<picture>');
});

it('serves the sponsor logo with a viewBox, so it scales', function () {
    $svg = file_get_contents(public_path('img/broker.svg'));

    // The file the old site shipped has width and height but no viewBox,
    // which pins it at 1569px wide.
    expect($svg)->toContain('viewBox="0 0 1569 1790"');
});
