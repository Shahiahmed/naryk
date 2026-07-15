<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Cache::forget('naryk.quotes');

    Http::fake([
        'apps.naryk.kz/*' => Http::response([
            'time' => '2026.07.14 18:00',
            'kase' => ['KSPI' => ['last' => '41400.00', 'status' => 'DOWN']],
        ]),
    ]);
});

it('runs the PR badge inline with the headline, not above it', function () {
    // Point 21.
    $post = Post::published()->newest()->firstOrFail();
    $post->pr_news = true;
    $post->save();

    $html = $this->get('/')->assertOk()->getContent();

    $badge = strpos($html, 'pr-badge');
    $heading = strrpos(substr($html, 0, $badge), '<h2 class="card__title');

    // The badge sits inside the heading, so the title carries on after it.
    expect($heading)->not->toBeFalse()
        ->and($badge - $heading)->toBeLessThan(200);
});

it('drops the time of day from a card', function () {
    // Point 28. The datetime attribute keeps the full stamp — that is what the
    // standard asks for, and it is not shown — so only check the visible text.
    $html = $this->get('/')->assertOk()->getContent();

    $shown = str($html)->after('meta__date')->after('>')->before('</time>')->toString();

    expect($shown)->toMatch('/\d{2}\.\d{2}\.\d{4}/')
        ->and($shown)->not->toMatch('/\d{2}:\d{2}/');
});

it('folds the side columns into the feed for phones', function () {
    // Point 22: banner after the third card, expert opinions after the sixth.
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('feed-block--phone')
        ->and($html)->toContain('Мамандар пікірі');
});

it('insets the ticker rather than bleeding it across the screen', function () {
    // Point 8.
    $this->get('/')->assertOk()->assertSee('class="ticker shell"', escape: false);
});

it('paints the ticker in the green of the wordmark', function () {
    // Point 2.
    $css = file_get_contents(public_path('assets/site.css'));

    expect($css)->toContain('--green-logo: #425e40')
        ->and($css)->toMatch('/\.ticker \{[^}]*background: var\(--green-logo\)/s');
});

it('gives the rubric strip and the ticker the same height', function () {
    // Point 3.
    $css = file_get_contents(public_path('assets/site.css'));

    expect($css)->toContain('--strip-height: 44px')
        ->and($css)->toMatch('/\.site-nav__inner \{[^}]*min-height: var\(--strip-height\)/s')
        ->and($css)->toMatch('/\.ticker \{[^}]*min-height: var\(--strip-height\)/s');
});

it('drives every gap from one spacing scale', function () {
    // Points 3, 9 and 15 all ask for the same rhythm site-wide.
    $css = file_get_contents(public_path('assets/site.css'));

    // Strip the :root block, where the scale itself is defined.
    $rules = preg_replace('/:root \{.*?\}/s', '', $css);

    // No raw pixel gaps or paddings outside the scale.
    preg_match_all('/(?:gap|padding|margin):\s*([^;]+);/', $rules, $matches);

    $raw = array_filter($matches[1], function (string $value): bool {
        return preg_match('/\b\d+px\b/', $value)
            && ! str_contains($value, 'var(')
            && ! preg_match('/^0|auto|1px|2px|3px/', trim($value));
    });

    expect($raw)->toBeEmpty('these gaps bypass the spacing scale: '.implode(' | ', $raw));
});

it('serves Roboto self-hosted and sets it on the body', function () {
    // Point 9: the font the old site used, the files the client sent.
    $css = file_get_contents(public_path('assets/site.css'));

    expect($css)->toContain('@font-face')
        ->and($css)->toContain('Roboto-Regular.woff2')
        ->and($css)->toContain('Roboto-Bold.woff2')
        ->and($css)->toMatch("/font-family: 'Roboto'/");

    expect(file_exists(public_path('fonts/Roboto-Regular.woff2')))->toBeTrue()
        ->and(file_exists(public_path('fonts/Roboto-Bold.woff2')))->toBeTrue();

    // Real woff2 files, not stray HTML error pages.
    expect(substr(file_get_contents(public_path('fonts/Roboto-Regular.woff2')), 0, 4))->toBe('wOF2');
});

it('keeps time in Almaty, where the client writes it', function () {
    /*
     * The publishing hours in the client's table run 09:00-18:00 — a working
     * day in Almaty, and nothing at all in UTC. Laravel's default would have
     * stamped new posts five hours early and dropped them into the wrong place
     * in a feed ordered by date.
     */
    expect(config('app.timezone'))->toBe('Asia/Almaty');

    $post = Post::published()->newest()->firstOrFail();

    expect($post->created_at->timezone->getName())->toBe('Asia/Almaty');
});

it('shows the press address in the footer, and no phone number', function () {
    // Points 25 and 26.
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('press.naryk@gmail.com')
        ->and($html)->not->toContain('tel:')
        ->and($html)->not->toContain('+7 778 112 1332');
});
